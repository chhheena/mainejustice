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
namespace JchOptimize\Core\Html;

\defined('_JCH_EXEC') or die('Restricted access');
use JchOptimize\Core\Combiner;
use JchOptimize\Core\Css\Processor as CssProcessor;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FeatureHelpers\DynamicJs;
use JchOptimize\Core\FeatureHelpers\Fonts;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\SerializableTrait;
use JchOptimize\Core\StorageTaggingTrait;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Profiler;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Cache\Pattern\CallbackCache;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Serializable;
use function ucfirst;
/**
 * Class CacheManager
 * @package JchOptimize\Core\Html
 *
 *          Handles the retrieval of contents from cache and hands over the repairing of the HTML to LinkBuilder
 */
class CacheManager implements LoggerAwareInterface, ContainerAwareInterface, Serializable
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use SerializableTrait;
    use StorageTaggingTrait;
    /**
     * @var Registry
     */
    private $params;
    /**
     * @var LinkBuilder
     */
    private $linkBuilder;
    /**
     * @var FilesManager
     */
    private $filesManager;
    /**
     * @var CallbackCache $callbackCache
     */
    private $callbackCache;
    /**
     * @var Combiner
     */
    private $combiner;
    /**
     * @var Http2Preload
     */
    private $http2Preload;
    /**
     * @var Processor
     */
    private $processor;
    /**
     * @var FileUtils
     */
    private $fileUtils;
    /**
     * @var TaggableInterface
     */
    private $taggableCache;
    /**
     * @param   Registry           $params
     * @param   LinkBuilder        $linkBuilder
     * @param   Combiner           $combiner
     * @param   FilesManager       $filesManager
     * @param   CallbackCache      $callbackCache
     * @param   TaggableInterface  $taggableCache
     * @param   Http2Preload       $http2Preload
     * @param   Processor          $processor
     * @param   FileUtils          $fileUtils
     */
    public function __construct(Registry $params, \JchOptimize\Core\Html\LinkBuilder $linkBuilder, Combiner $combiner, \JchOptimize\Core\Html\FilesManager $filesManager, CallbackCache $callbackCache, TaggableInterface $taggableCache, Http2Preload $http2Preload, \JchOptimize\Core\Html\Processor $processor, FileUtils $fileUtils)
    {
        $this->params = $params;
        $this->linkBuilder = $linkBuilder;
        $this->combiner = $combiner;
        $this->filesManager = $filesManager;
        $this->callbackCache = $callbackCache;
        $this->taggableCache = $taggableCache;
        $this->http2Preload = $http2Preload;
        $this->processor = $processor;
        $this->fileUtils = $fileUtils;
    }
    /**
     * @throws Exception\ExceptionInterface
     */
    public function handleCombineJsCss()
    {
        if (!\function_exists("array_key_last")) {
            function array_key_last($array)
            {
                if (!\is_array($array) || empty($array)) {
                    return null;
                }
                return \array_keys($array)[\count($array) - 1];
            }
        }
        //Indexed multidimensional array of files to be combined
        $aCssLinksArray = $this->filesManager->aCss;
        $aJsLinksArray = $this->filesManager->aJs;
        $section = $this->params->get('bottom_js', '0') == '1' ? 'body' : 'head';
        if (!Helper::isMsieLT10() && $this->params->get('combine_files_enable', '1')) {
            $bCombineCss = (bool) $this->params->get('css', 1);
            $bCombineJs = (bool) $this->params->get('js', 1);
            if ($bCombineCss && !empty($aCssLinksArray[0])) {
                /** @var CssProcessor $oCssProcessor */
                $oCssProcessor = $this->container->get(CssProcessor::class);
                $pageCss = '';
                $cssUrls = [];
                foreach ($aCssLinksArray as $aCssLinks) {
                    //Optimize and cache css files
                    $aCssCache = $this->getCombinedFiles($aCssLinks, $sCssCacheId, 'css');
                    if (JCH_PRO && !empty($aCssCache['font-face'])) {
                        Fonts::appendOptimizedFontsToHtml($this->container, $aCssCache['font-face']);
                    }
                    //If Optimize CSS Delivery feature not enabled then we'll need to insert the link to
                    //the combined css file in the HTML
                    if (!$this->params->get('optimizeCssDelivery_enable', '0')) {
                        //Http2Preload push
                        $oCssProcessor->preloadHttp2($aCssCache['contents'], \true);
                        $this->linkBuilder->replaceLinks($sCssCacheId, 'css');
                    } else {
                        $pageCss .= $aCssCache['contents'];
                        $cssUrls[] = ['href' => $this->linkBuilder->buildUrl($sCssCacheId, 'css'), 'as' => 'style', 'onload' => 'rel=\'stylesheet\''];
                    }
                }
                $css_delivery_enabled = $this->params->get('optimizeCssDelivery_enable', '0');
                if ($css_delivery_enabled) {
                    try {
                        $sCriticalCss = $this->getCriticalCss($oCssProcessor, $pageCss, $id);
                        //Http2Preload push fonts in critical css
                        $oCssProcessor->preloadHttp2($sCriticalCss);
                        $this->linkBuilder->addCriticalCssToHead($sCriticalCss, $id);
                        $this->linkBuilder->loadCssAsync($cssUrls);
                    } catch (Exception\ExceptionInterface $oException) {
                        $this->logger->error('Optimize CSS Delivery failed: ' . $oException->getMessage());
                        //@TODO Just add CssUrls to HEAD section of document
                    }
                }
            }
            if ($bCombineJs) {
                $this->linkBuilder->addExcludedJsToSection($section);
                if (!empty($aJsLinksArray[0])) {
                    foreach ($aJsLinksArray as $aJsLinksKey => $aJsLinks) {
                        //Optimize and cache javascript files
                        $this->getCombinedFiles($aJsLinks, $sJsCacheId, 'js');
                        //Insert link to combined javascript file in HTML
                        $this->linkBuilder->replaceLinks($sJsCacheId, 'js', $section, $aJsLinksKey);
                    }
                }
                if (JCH_PRO) {
                    /** @see DynamicJs::appendCriticalJsToHtml() */
                    $this->container->get(DynamicJs::class)->appendCriticalJsToHtml();
                }
                //We also now append any deferred javascript files below the
                //last combined javascript file
                $this->linkBuilder->addDeferredJs($section);
            }
        }
        if ($this->params->get('lazyload_enable', '0')) {
            $jsLazyLoadAssets = $this->getJsLazyLoadAssets();
            $this->getCombinedFiles($jsLazyLoadAssets, $lazyLoadCacheId, 'js');
            $this->linkBuilder->addJsLazyLoadAssetsToHtml($lazyLoadCacheId, $section);
        }
        $this->linkBuilder->appendAsyncScriptsToHead();
    }
    private function getJsLazyLoadAssets() : array
    {
        $assets = [];
        $assets[]['url'] = Paths::mediaUrl(\true) . '/core/js/ls.loader.js?' . JCH_VERSION;
        if (JCH_PRO && $this->params->get('pro_lazyload_effects', '0')) {
            $assets[]['url'] = Paths::mediaUrl(\true) . '/core/js/ls.loader.effects.js?' . JCH_VERSION;
        }
        if (JCH_PRO && ($this->params->get('pro_lazyload_bgimages', '0') || $this->params->get('pro_lazyload_audiovideo', '0'))) {
            $assets[]['url'] = Paths::mediaUrl(\true) . '/lazysizes/ls.unveilhooks.min.js?' . JCH_VERSION;
        }
        $assets[]['url'] = Paths::mediaUrl(\true) . '/lazysizes/lazysizes.min.js?' . JCH_VERSION;
        return $assets;
    }
    /**
     * Returns contents of the combined files from cache
     *
     * @param   array        $links  Indexed multidimensional array of file urls to combine
     * @param   string|null  $id     Id of generated cache file
     * @param   string       $type   css or js
     *
     * @return array Contents in array from cache containing combined file(s)
     */
    public function getCombinedFiles(array $links, ?string &$id, string $type)
    {
        JCH_DEBUG ? Profiler::start('GetCombinedFiles - ' . $type) : null;
        $aArgs = [$links];
        /**
         * @see Combiner::getCssContents()
         * @see Combiner::getJsContents()
         */
        $aFunction = [$this->combiner, 'get' . ucfirst($type) . 'Contents'];
        $aCachedContents = $this->loadCache($aFunction, $aArgs, $id);
        JCH_DEBUG ? Profiler::stop('GetCombinedFiles - ' . $type, \true) : null;
        return $aCachedContents;
    }
    /**
     * @param   array        $ids          Ids of files that are already combined
     * @param   array        $fileMatches  Array matches of file to be appended to the combined file
     * @param   string|null  $id
     *
     * @return array|bool
     */
    public function getAppendedFiles(array $ids, array $fileMatches, ?string &$id)
    {
        JCH_DEBUG ? Profiler::start('GetAppendedFiles') : null;
        $args = [$ids, $fileMatches, 'js'];
        $function = [$this->combiner, 'appendFiles'];
        $cachedContents = $this->loadCache($function, $args, $id);
        JCH_DEBUG ? Profiler::stop('GetAppendedFiles', \true) : null;
        return $cachedContents;
    }
    /**
     * Create and cache aggregated file if it doesn't exist and also tag the cache with the current page url
     *
     * @param   callable     $function  Name of function used to aggregate filesG
     * @param   array        $args      Arguments used by function above
     * @param   string|null  $id        Generated id to identify cached file
     *
     * @return  bool|array  The contents of the combined file
     *
     * @throws Exception\RuntimeException
     */
    private function loadCache(callable $function, array $args, ?string &$id)
    {
        try {
            $id = $this->callbackCache->generateKey($function, $args);
            $results = $this->callbackCache->call($function, $args);
            $this->tagStorage($id);
            //Returns the contents of the combined file or false if failure
            return $results;
        } catch (\Exception $e) {
            throw new Exception\RuntimeException('Error creating cache files: ' . $e->getMessage());
        }
    }
    /**
     * @throws Exception\MissingDependencyException
     */
    protected function getCriticalCss($oCssProcessor, $pageCss, ?string &$iCacheId)
    {
        if (!\class_exists('DOMDocument') || !\class_exists('DOMXPath')) {
            throw new Exception\MissingDependencyException('Document Object Model not supported');
        } else {
            $html = $this->processor->cleanHtml();
            //Remove text nodes from HTML elements
            $html = \preg_replace_callback('#(<(?>[^<>]++|(?1))*+>)|((?<=>)(?=[^<>\\S]*+[^<>\\s])[^<>]++)#', function ($m) {
                if (!empty($m[1])) {
                    return $m[0];
                }
                if (!empty($m[2])) {
                    return ' ';
                }
            }, $html);
            //Truncate HTML to 400 elements to key cache
            $htmlKey = '';
            \preg_replace_callback('#<(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+(<ul\\b[^>]*+>(?>[^<]*+<(?!ul)[^<]*+|(?1))*?</ul>)?)*?(?=<[a-z0-9])#i', function ($aM) use(&$htmlKey) {
                $htmlKey .= $aM[0];
                return;
            }, $html, 400);
            $aArgs = [$pageCss, $htmlKey];
            $aFunction = [$oCssProcessor, 'optimizeCssDelivery'];
            return $this->loadCache($aFunction, $aArgs, $iCacheId);
        }
    }
    /**
     *
     *
     */
    public function handleImgAttributes()
    {
        if (!empty($this->processor->images)) {
            JCH_DEBUG ? Profiler::start('AddImgAttributes') : null;
            try {
                $aImgAttributes = $this->loadCache([$this, 'getCachedImgAttributes'], [$this->processor->images], $id);
            } catch (Exception\ExceptionInterface $e) {
                return;
            }
            $this->linkBuilder->setImgAttributes($aImgAttributes);
        }
        JCH_DEBUG ? Profiler::stop('AddImgAttributes', \true) : null;
    }
    /**
     *
     * @param   array  $aImages
     *
     * @return array
     */
    public function getCachedImgAttributes(array $aImages) : array
    {
        $aImgAttributes = array();
        $total = \count($aImages[0]);
        for ($i = 0; $i < $total; $i++) {
            if ($aImages[2][$i]) {
                //delimiter
                $delim = $aImages[3][$i];
                //Image url
                $url = $aImages[4][$i];
            } else {
                $delim = $aImages[6][$i];
                $url = $aImages[7][$i];
            }
            if (Url::isInvalid($url) || !$this->filesManager->isHttpAdapterAvailable($url) || Url::isSSL($url) && !\extension_loaded('openssl') || !Url::isHttpScheme($url)) {
                $aImgAttributes[] = $aImages[0][$i];
                continue;
            }
            $sPath = $this->fileUtils->getPath($url);
            if (\file_exists($sPath)) {
                $aSize = \getimagesize($sPath);
                if ($aSize === \false || empty($aSize) || $aSize[0] == '1' && $aSize[1] == '1') {
                    $aImgAttributes[] = $aImages[0][$i];
                    continue;
                }
                $u = \JchOptimize\Core\Html\Parser::HTML_ATTRIBUTE_VALUE();
                $isImageAttrEnabled = $this->params->get('img_attributes_enable', '0');
                //Checks for any existing width attribute
                if (\preg_match("#\\swidth\\s*+=\\s*+['\"]?({$u})#i", $aImages[0][$i], $aMatches)) {
                    //Calculate height based on aspect ratio
                    $iWidthAttrValue = \preg_replace('#[^0-9]#', '', $aMatches[1]);
                    $height = \round($aSize[1] / $aSize[0] * $iWidthAttrValue, 2);
                    //If add attributes not enabled put data-height instead
                    $heightAttribute = $isImageAttrEnabled ? 'height=' : 'data-height=';
                    $heightAttribute .= $delim . $height . $delim;
                    //Add height attribute to the img element and save in array
                    $aImgAttributes[] = \preg_replace('#\\s*+/?>$#', ' ' . $heightAttribute . ' />', $aImages[0][$i]);
                } elseif (\preg_match("#\\sheight\\s*+=\\s*=['\"]?({$u})#i", $aImages[0][$i], $aMatches)) {
                    //Calculate width based on aspect ratio
                    $iHeightAttrValue = \preg_replace('#[^0-9]#', '', $aMatches[1]);
                    $width = \round($aSize[0] / $aSize[1] * $iHeightAttrValue, 2);
                    //if add attributes not enabled put data-width instead
                    $widthAttribute = $isImageAttrEnabled ? 'width=' : 'data-width=';
                    $widthAttribute .= $delim . $width . $delim;
                    //Add width attribute to the img element and save in array
                    $aImgAttributes[] = \preg_replace('#\\s*+/?>$#', ' ' . $widthAttribute . ' />', $aImages[0][$i]);
                } else {
                    //No existing attributes, just go ahead and add attributes from getimagesize
                    //It's best to use the same delimiter for the width/height attributes that the urls used
                    $sReplace = ' ' . \str_replace('"', $delim, $aSize[3]);
                    //Add the width and height attributes from the getimagesize function
                    $sReplace = \preg_replace('#\\s*+/?>$#', $sReplace . ' />', $aImages[0][$i]);
                    if (!$isImageAttrEnabled) {
                        $sReplace = \str_replace(array('width=', 'height='), array('data-width=', 'data-height='), $sReplace);
                    }
                    $aImgAttributes[] = $sReplace;
                }
            } else {
                $aImgAttributes[] = $aImages[0][$i];
            }
        }
        return $aImgAttributes;
    }
}
