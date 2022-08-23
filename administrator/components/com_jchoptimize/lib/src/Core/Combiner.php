<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core;

\defined('_JCH_EXEC') or die('Restricted access');
use CodeAlfa\Minify\Css;
use CodeAlfa\Minify\Js;
use CodeAlfa\RegexTokenizer\Debug\Debug;
use Exception;
use JchOptimize\Core\Css\Processor as CssProcessor;
use JchOptimize\Core\Css\Sprite\Generator;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Profiler;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Cache\Pattern\CallbackCache;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Serializable;
/**
 * Class to combine CSS/JS files together
 */
class Combiner implements ContainerAwareInterface, LoggerAwareInterface, Serializable
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use Debug;
    use \JchOptimize\Core\FileInfosUtilsTrait;
    use \JchOptimize\Core\SerializableTrait;
    use \JchOptimize\Core\StorageTaggingTrait;
    /**
     * @var bool
     */
    public $isBackend;
    /**
     * @var Registry
     */
    private $params;
    /**
     * @var CallbackCache $callbackCache
     */
    private $callbackCache;
    /**
     * @var ClientInterface|null
     */
    private $http;
    /**
     * @var TaggableInterface
     */
    private $taggableCache;
    /**
     * Constructor
     *
     * @param   Registry              $params
     * @param   CallbackCache         $callbackCache
     * @param   ClientInterface|null  $http
     * @param   bool                  $isBackend
     */
    public function __construct(Registry $params, CallbackCache $callbackCache, TaggableInterface $taggableCache, \JchOptimize\Core\FileUtils $fileUtils, ?ClientInterface $http, bool $isBackend = \false)
    {
        $this->params = $params;
        $this->callbackCache = $callbackCache;
        $this->taggableCache = $taggableCache;
        $this->fileUtils = $fileUtils;
        $this->http = $http;
        $this->isBackend = $isBackend;
    }
    /**
     * Get aggregated and possibly minified content from js and css files
     *
     * @param   array   $urlArray  Indexed multidimensional array of urls of css or js files for aggregation
     * @param   string  $type      css or js
     *
     * @return array   Aggregated (and possibly minified) contents of files
     */
    public function getContents(array $urlArray, string $type) : array
    {
        JCH_DEBUG ? Profiler::start('GetContents - ' . $type, \true) : null;
        $aResult = $this->combineFiles($urlArray, $type);
        $sContents = $this->prepareContents($aResult['content']);
        if ($type == 'css') {
            if ($this->params->get('csg_enable', 0)) {
                try {
                    $oSpriteGenerator = $this->container->get(Generator::class);
                    $aSpriteCss = $oSpriteGenerator->getSprite($sContents);
                    if (!empty($aSpriteCss) && !empty($aSpriteCss['needles']) && !empty($aSpriteCss['replacements'])) {
                        $sContents = \str_replace($aSpriteCss['needles'], $aSpriteCss['replacements'], $sContents);
                    }
                } catch (Exception $ex) {
                    $this->logger->error($ex->getMessage());
                }
            }
            $sContents = $aResult['import'] . $sContents;
            if (\function_exists('mb_convert_encoding')) {
                $sContents = '@charset "utf-8";' . $sContents;
            }
        }
        //Save contents in array to store in cache
        $aContents = array('filemtime' => \time(), 'etag' => \md5($sContents), 'contents' => $sContents, 'images' => \array_unique($aResult['images']), 'font-face' => $aResult['font-face']);
        JCH_DEBUG ? Profiler::stop('GetContents - ' . $type) : null;
        return $aContents;
    }
    public function getCssContents(array $urlArray) : array
    {
        return $this->getContents($urlArray, 'css');
    }
    public function getJsContents(array $urlArray) : array
    {
        return $this->getContents($urlArray, 'js');
    }
    /**
     * Aggregate contents of CSS and JS files
     *
     * @param   array   $fileInfosArray  Array of links of files to combine
     * @param   string  $type            css|js
     *
     * @return array               Aggregated contents
     */
    public function combineFiles(array $fileInfosArray, string $type, $cacheItems = \true) : array
    {
        $responses = ['content' => '', 'import' => '', 'font-face' => [], 'images' => []];
        //Iterate through each file/script to optimize and combine
        foreach ($fileInfosArray as $fileInfos) {
            //Truncate url to less than 40 characters
            $sUrl = $this->prepareFileUrl($fileInfos, $type);
            JCH_DEBUG ? Profiler::start('CombineFile - ' . $sUrl) : null;
            if ($cacheItems) {
                $function = array($this, 'cacheContent');
                $args = array($fileInfos, $type, \true);
                //Optimize and cache file/script returning the optimized content
                $id = $this->callbackCache->generateKey($function, $args);
                $results = $this->callbackCache->call($function, $args);
                $this->tagStorage($id);
                //Append to combined contents
                $responses['content'] .= $this->addCommentedUrl($type, $fileInfos) . $results['content'] . "\n" . 'DELIMITER';
            } else {
                //If we're not caching just get the optimized content
                $results = $this->cacheContent($fileInfos, $type, \false);
                $responses['content'] .= $this->addCommentedUrl($type, $fileInfos) . $results['content'] . '|"LINE_END"|';
            }
            if ($type == 'css') {
                $responses['import'] .= $results['import'];
                $responses['images'] = \array_merge($responses['images'], $results['images']);
                if (!empty($results['font-face'])) {
                    $responses['font-face'][] = $results['font-face'];
                }
            }
            JCH_DEBUG ? Profiler::stop('CombineFile - ' . $sUrl, \true) : null;
        }
        return $responses;
    }
    /**
     *
     * @param   string  $type
     * @param   array   $fileInfos
     *
     * @return string
     */
    protected function addCommentedUrl(string $type, array $fileInfos) : string
    {
        $comment = '';
        if ($this->params->get('debug', '1')) {
            $fileInfos = $fileInfos['url'] ?? ($type == 'js' ? 'script' : 'style') . ' declaration';
            $comment = '|"COMMENT_START ' . $fileInfos . ' COMMENT_END"|';
        }
        return $comment;
    }
    /**
     * Optimize and cache contents of individual file/script returning optimized content
     *
     * @param   array    $fileInfos
     * @param   string   $type
     * @param   boolean  $bPrepare
     *
     * @return array
     */
    public function cacheContent(array $fileInfos, string $type, bool $bPrepare) : array
    {
        //Initialize content string
        $content = '';
        $responses = [];
        //If it's a file fetch the contents of the file
        if (isset($fileInfos['url'])) {
            //Convert local urls to file path
            $path = $this->fileUtils->getPath($fileInfos['url']);
            $content .= $this->getFileContents($path);
            if (\defined('JCH_TEST_MODE')) {
                $content = \str_replace(['{{{domain}}}', '{{{altdomain}}}', '{{{base}}}'], [TEST_SITE_DOMAIN, TEST_SITE_ALT_DOMAIN, TEST_SITE_BASE], $content);
            }
        } else {
            //If it's a declaration just use it
            $content .= $fileInfos['content'];
        }
        if ($type == 'css') {
            /** @var CssProcessor $oCssProcessor */
            $oCssProcessor = $this->container->get(CssProcessor::class);
            $oCssProcessor->setCssInfos($fileInfos);
            $oCssProcessor->setCss($content);
            $oCssProcessor->formatCss();
            $oCssProcessor->processUrls(\false, \false, $this->isBackend);
            $oCssProcessor->processMediaQueries();
            $oCssProcessor->processAtRules();
            $content = $oCssProcessor->getCss();
            $responses['import'] = $oCssProcessor->getImports();
            $responses['images'] = $oCssProcessor->getImages();
            $responses['font-face'] = $oCssProcessor->getFontFace();
        }
        if ($type == 'js' && \trim($content) != '') {
            if ($this->params->get('try_catch', '1')) {
                $content = $this->addErrorHandler($content, $fileInfos);
            } else {
                $content = $this->addSemiColon($content);
            }
        }
        if ($bPrepare) {
            $content = $this->minifyContent($content, $type, $fileInfos);
            $content = $this->prepareContents($content);
        }
        $responses['content'] = $content;
        return $responses;
    }
    /**
     * Used when you want to append the contents of files to some that are already combined, into one file
     *
     * @param   array   $ids          Array of ids of files that were already combined
     * @param   array   $fileMatches  Array of file matches to be combined
     * @param   string  $type         Type of files css|js
     *
     * @return array The contents of the combined files
     */
    public function appendFiles(array $ids, array $fileMatches, string $type) : array
    {
        $contents = '';
        foreach ($ids as $id) {
            $contents .= \JchOptimize\Core\Output::getCombinedFile(['f' => $id, 'type' => $type], \false);
        }
        try {
            $results = $this->combineFiles($fileMatches, $type);
        } catch (Exception $e) {
            $this->logger->error('Error appending files: ' . $e->getMessage());
            $results = ['content' => '', 'font-face' => [], 'gfonts' => [], 'images' => []];
        }
        $contents .= $this->prepareContents($results['content']);
        //$contents .= "\n" . 'jchOptimizeDynamicScriptLoader.next()';
        return ['filemtime' => \time(), 'etag' => \md5($contents), 'contents' => $contents, 'font-face' => $results['font-face'], 'images' => $results['images']];
    }
    private function getFileContents(string $path) : string
    {
        //We need to use the http client if it's a remote or dynamic file
        if (\strpos($path, 'http') === 0) {
            try {
                $response = $this->http->get($path, ['Accept-Encoding' => 'identity;q=0']);
                if ($response->getStatusCode() == '200') {
                    //Get body and set pointer to beginning of stream
                    $body = $response->getBody();
                    $body->rewind();
                    return $body->getContents();
                } else {
                    return '|"COMMENT_START Response returned status code: ' . $response->getStatusCode() . ' COMMENT_END"|';
                }
            } catch (Exception $e) {
                return '|"COMMENT_START Exception fetching file with message: ' . $e->getMessage() . ' COMMENT_END"|';
            }
        } else {
            if (\file_exists($path)) {
                return @\file_get_contents($path);
            } else {
                //Probably a rewrite file
                $urlPath = Paths::path2Url($path);
                //Check again to make sure it exists to avoid recursive call if redirected to
                //Not found error page.
                if (\file_exists($urlPath)) {
                    try {
                        $response = $this->http->get($urlPath, ['Accept-Encoding' => 'identity;q=0']);
                        if ($response->getStatusCode() == '200') {
                            $body = $response->getBody();
                            $body->rewind();
                            return $body->getContents();
                        }
                    } catch (Exception $e) {
                        return '|"COMMENT_START Exception fetching file with message: ' . $e->getMessage() . ' COMMENT_END"|';
                    }
                }
                return '|"COMMENT_START File [' . $path . '] not found COMMENT_END"|';
            }
        }
    }
    /**
     * Add try catch to contents of javascript file
     *
     * @param   string  $content
     * @param   array   $fileInfos
     *
     * @return string
     */
    private function addErrorHandler(string $content, array $fileInfos) : string
    {
        if (empty($fileInfos['module']) || $fileInfos['module'] != 'module') {
            $content = 'try {' . "\n" . $content . "\n" . '} catch (e) {' . "\n";
            $content .= 'console.error(\'Error in ';
            $content .= isset($fileInfos['url']) ? 'file:' . $fileInfos['url'] : 'script declaration';
            $content .= '; Error:\' + e.message);' . "\n" . '};';
        }
        return $content;
    }
    /**
     * Add semicolon to end of js files if non exists;
     *
     * @param   string  $content
     *
     * @return string
     */
    private function addSemiColon(string $content) : string
    {
        $content = \rtrim($content);
        if (\substr($content, -1) != ';' && !\preg_match('#\\|"COMMENT_START File[^"]+not found COMMENT_END"\\|#', $content)) {
            $content = $content . ';';
        }
        return $content;
    }
    /**
     * Minify contents of fil
     *
     * @param   string  $content
     * @param   string  $type
     * @param   array   $fileInfos
     *
     * @return string $sMinifiedContent Minified content or original content if failed
     */
    private function minifyContent(string $content, string $type, array $fileInfos) : string
    {
        if ($this->params->get($type . '_minify', 0)) {
            $url = $this->prepareFileUrl($fileInfos, $type);
            $minifiedContent = \trim($type == 'css' ? Css::optimize($content) : Js::optimize($content));
            /* @TODO inject Exception class into minifier libraries */
            if (\preg_last_error() !== 0) {
                $this->logger->error(\sprintf('Error occurred trying to minify: %s', $url));
                $minifiedContent = $content;
            }
            $this->_debug($url, '', 'minifyContent');
            return $minifiedContent;
        }
        return $content;
    }
    /**
     * Remove placeholders from aggregated file for caching
     *
     * @param   string  $contents  Aggregated file contents
     * @param   bool    $test
     *
     * @return string
     */
    private function prepareContents(string $contents, bool $test = \false) : string
    {
        return \str_replace(['|"COMMENT_START', '|"COMMENT_IMPORT_START', 'COMMENT_END"|', 'DELIMITER', '|"LINE_END"|'], ["\n" . '/***! ', "\n" . "\n" . '/***! @import url', ' !***/' . "\n" . "\n", $test ? 'DELIMITER' : '', "\n"], \trim($contents));
    }
}
