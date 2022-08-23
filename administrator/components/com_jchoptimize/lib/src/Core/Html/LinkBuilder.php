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
use JchOptimize\Core\Cdn;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FeatureHelpers\CdnDomains;
use JchOptimize\Core\FeatureHelpers\DynamicJs;
use JchOptimize\Core\FeatureHelpers\GoogleFonts;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\Output;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Utility;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use _JchOptimizeVendor\Laminas\Cache\Storage\FlushableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
/**
 *
 *
 */
class LinkBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var Parser $oProcessor
     */
    private $oProcessor;
    /**
     * @var string cache id *
     */
    private $params;
    /**
     * @var AsyncManager
     */
    private $asyncManager;
    /**
     * @var FilesManager
     */
    private $filesManager;
    /**
     * @var StorageInterface $cache
     */
    private $cache;
    /**
     * @var Cdn
     */
    private $cdn;
    /**
     * @var Http2Preload
     */
    private $http2Preload;
    /**
     * @var FileUtils
     * @since version
     */
    private $fileUtils;
    /**
     * Constructor
     *
     * @param   Registry          $params
     * @param   Processor         $processor
     * @param   FilesManager      $filesManager
     * @param   Cdn               $cdn
     * @param   Http2Preload      $http2Preload
     * @param   StorageInterface  $cache
     * @param   FileUtils         $fileUtils
     */
    public function __construct(Registry $params, \JchOptimize\Core\Html\Processor $processor, \JchOptimize\Core\Html\FilesManager $filesManager, Cdn $cdn, Http2Preload $http2Preload, StorageInterface $cache, FileUtils $fileUtils)
    {
        $this->params = $params;
        $this->oProcessor = $processor;
        $this->filesManager = $filesManager;
        $this->cdn = $cdn;
        $this->http2Preload = $http2Preload;
        $this->cache = $cache;
        $this->fileUtils = $fileUtils;
        if (JCH_PRO) {
            $this->asyncManager = new \JchOptimize\Core\Html\AsyncManager($params);
        }
    }
    /**
     * Add preconnect elements for Google Font files and CDN domains
     * Used by PRO_ONLY
     */
    public function addPreConnects()
    {
        /** @var GoogleFonts $googleFonts */
        $googleFonts = $this->container->get(GoogleFonts::class);
        if (!$googleFonts->isGFontPreConnected() && $googleFonts->isGoogleFontsOptimized) {
            $this->prependChildToHead($googleFonts->getPreconnect());
        }
        /** @see CdnDomains::preconnect() */
        $this->container->get(CdnDomains::class)->preconnect();
    }
    private function prependChildToHead($sChild)
    {
        $sHeadHtml = \preg_replace('#<head[^>]*+>#i', '<head>' . "\n\t" . $sChild, $this->oProcessor->getHeadHtml(), 1);
        $this->oProcessor->setHeadHtml($sHeadHtml);
    }
    public function addOptimizeGFontsToHead()
    {
        /** @see GoogleFonts::$googleFonts $googleFonts */
        $googleFonts = $this->container->get(GoogleFonts::class)->googleFonts;
        $this->prependChildToHead(\implode("\n", \array_unique($googleFonts)));
    }
    private function appendChildToHead($sChild, $bCleanReplacement = \false)
    {
        if ($bCleanReplacement) {
            $sChild = Helper::cleanReplacement($sChild);
        }
        $sHeadHtml = $this->oProcessor->getHeadHtml();
        $sHeadHtml = \preg_replace('#' . \JchOptimize\Core\Html\Parser::HTML_END_HEAD_TAG() . '#i', $sChild . "\n\t" . '</head>', $sHeadHtml, 1);
        $this->oProcessor->setHeadHtml($sHeadHtml);
    }
    public function appendOptimizedFontsToHead($fontFile)
    {
        $this->appendChildToHead($fontFile);
    }
    public function addCriticalCssToHead($sCriticalCss, $id)
    {
        $sCriticalStyle = '<style id="jch-optimize-critical-css" data-id="' . $id . '">' . "\n" . $sCriticalCss . "\n" . '</style>';
        $this->appendChildToHead($sCriticalStyle, \true);
    }
    public function addExcludedJsToSection($section)
    {
        $aExcludedJs = $this->filesManager->aExcludedJs;
        //Add excluded javascript files to the bottom of the HTML section
        $sExcludedJs = \implode("\n", $aExcludedJs['ieo']) . \implode("\n", $aExcludedJs['peo']);
        $sExcludedJs = Helper::cleanReplacement($sExcludedJs);
        if ($sExcludedJs != '') {
            $this->appendChildToHTML($sExcludedJs, $section);
        }
    }
    private function appendChildToHTML($child, $section)
    {
        $sSearchArea = \preg_replace('#' . \JchOptimize\Core\Html\Parser::{'HTML_END_' . \strtoupper($section) . '_TAG'}() . '#si', "\t" . $child . "\n" . '</' . $section . '>', $this->oProcessor->getFullHtml(), 1);
        $this->oProcessor->setFullHtml($sSearchArea);
    }
    public function addDeferredJs($section)
    {
        $defers = $this->filesManager->defers;
        //If we're loading javascript dynamically add the deferred javascript files to array of files to load dynamically instead
        if ($this->params->get('pro_remove_unused_js_enable', '0')) {
            /** @see DynamicJs::prepareJsDynamicUrls() */
            $this->container->get(DynamicJs::class)->prepareJsDynamicUrls($defers);
        }
        //Anything in matches array will be appended to bottom of file
        if (!empty($defers['matches'])) {
            $defers = \implode("\n", $defers['matches']);
            $this->appendChildToHTML($defers, $section);
        }
    }
    public function setImgAttributes($aCachedImgAttributes)
    {
        $sHtml = $this->oProcessor->getBodyHtml();
        $this->oProcessor->setBodyHtml(\str_replace($this->oProcessor->images[0], $aCachedImgAttributes, $sHtml));
    }
    /**
     * Insert url of aggregated file in html
     *
     * @param   string  $id
     * @param   string  $type
     * @param   string  $section     Whether section being processed is head|body
     * @param   int     $jsLinksKey  Index key of javascript combined file
     *
     * @throws Exception\RuntimeException
     */
    public function replaceLinks(string $id, string $type, string $section = 'head', int $jsLinksKey = 0)
    {
        JCH_DEBUG ? Profiler::start('ReplaceLinks - ' . $type) : null;
        $searchArea = $this->oProcessor->getFullHtml();
        //All js files after the last excluded js will be placed at bottom of section
        if ($type == 'js' && $jsLinksKey >= $this->filesManager->jsExcludedIndex && !empty($this->filesManager->aJs[$this->filesManager->iIndex_js])) {
            //If Remove Unused js enabled we'll simply add these files to array to be dynamically loaded instead
            if ($this->params->get('pro_remove_unused_js_enable', '0')) {
                DynamicJs::$jsDynamicIds[] = $id;
                return;
            }
            $url = $this->buildUrl($id, 'js');
            //If last combined file is being inserted at the bottom of the page then
            //add the async or defer attribute
            if ($section == 'body') {
                $defer = \false;
                $async = \false;
                if ($this->params->get('loadAsynchronous', '0')) {
                    if ($this->filesManager->bLoadJsAsync) {
                        $async = \true;
                    } else {
                        $defer = \true;
                    }
                }
                //Add async attribute to last combined js file if option is set
                $newLink = $this->getNewJsLink($url, $defer, $async);
            } else {
                $newLink = $this->getNewJsLink($url);
            }
            //Insert script tag at the appropriate section in the HTML
            $searchArea = \preg_replace('#' . \JchOptimize\Core\Html\Parser::{'HTML_END_' . \strtoupper($section) . '_TAG'}() . '#si', "\t" . $newLink . "\n" . '</' . $section . '>', $searchArea, 1);
            $deferred = $this->filesManager->isFileDeferred($newLink);
            $this->http2Preload->add($url, $type, $deferred);
        } else {
            $url = $this->buildUrl($id, $type);
            $this->http2Preload->add($url, $type);
            $newLink = $this->{'getNew' . \ucfirst($type) . 'Link'}($url);
            //Replace placeholders in HTML with combined files
            $searchArea = \preg_replace('#<JCH_' . \strtoupper($type) . '([^>]++)>#', $newLink, $searchArea, 1);
        }
        $this->oProcessor->setFullHtml($searchArea);
        JCH_DEBUG ? Profiler::stop('ReplaceLinks - ' . $type, \true) : null;
    }
    /**
     * Returns url of aggregated file
     *
     * @param   string  $id
     * @param   string  $type  css or js
     *
     * @return string  Url of aggregated file
     */
    public function buildUrl(string $id, string $type) : string
    {
        $htaccess = $this->params->get('htaccess', 2);
        switch ($htaccess) {
            case '1':
            case '3':
                $path = Paths::relAssetPath();
                $path = $htaccess == 3 ? $path . '3' : $path;
                $url = $path . Paths::rewriteBaseFolder() . ($this->isGz() ? 'gz' : 'nz') . '/' . $id . '.' . $type;
                break;
            case '0':
                $oUri = new Uri(Paths::relAssetPath());
                $oUri->setPath($oUri->getPath() . '2/jscss.php');
                $aVar = array();
                $aVar['f'] = $id;
                $aVar['type'] = $type;
                $aVar['gz'] = $this->isGZ() ? 'gz' : 'nz';
                $oUri->setQuery($aVar);
                $url = \htmlentities($oUri->toString());
                break;
            case '2':
            default:
                $path = Paths::cachePath();
                $url = $path . '/' . $type . '/' . $id . '.' . $type;
                // . ($this->isGz() ? '.gz' : '');
                $this->createStaticFiles($id, $type, $url);
                break;
        }
        if ($this->params->get('cookielessdomain_enable', '0') && !Url::isRootRelative($url)) {
            $url = Url::toRootRelative($url);
        }
        return $this->cdn->loadCdnResource($url);
    }
    /**
     * Check if gzip is set or enabled
     *
     * @return boolean   True if gzip parameter set and server is enabled
     */
    public function isGZ() : bool
    {
        return $this->params->get('gzip', 0) && \extension_loaded('zlib') && !\ini_get('zlib.output_compression') && \ini_get('output_handler') != 'ob_gzhandler';
    }
    /**
     * Create static combined file if not yet exists
     *
     *
     * @param   string  $id    Cache id of file
     * @param   string  $type  Type of file css|js
     * @param   string  $url   Url of combine file
     *
     * @return void
     */
    protected function createStaticFiles(string $id, string $type, string $url) : void
    {
        JCH_DEBUG ? Profiler::start('CreateStaticFiles - ' . $type) : null;
        //File path of combined file
        $combinedFile = $this->fileUtils->getPath($url);
        if (!\file_exists($combinedFile)) {
            $vars = ['f' => $id, 'type' => $type];
            $content = Output::getCombinedFile($vars, \false);
            if ($content === \false) {
                throw new Exception\RuntimeException('Error retrieving combined contents');
            }
            //Create file and any directory
            if (!File::write($combinedFile, $content)) {
                if ($this->cache instanceof FlushableInterface) {
                    $this->cache->flush();
                }
                throw new Exception\RuntimeException('Error creating static file');
            }
        }
        JCH_DEBUG ? Profiler::stop('CreateStaticFiles - ' . $type, \true) : null;
    }
    /**
     * Determine if document is of XHTML doctype
     *
     * @return boolean
     */
    public function isXhtml() : bool
    {
        return (bool) \preg_match('#^\\s*+(?:<!DOCTYPE(?=[^>]+XHTML)|<\\?xml.*?\\?>)#i', \trim($this->oProcessor->getHtml()));
    }
    /**
     * @param $cssUrls
     */
    public function loadCssAsync($cssUrls)
    {
        if (!$this->params->get('pro_remove_unused_css', '0')) {
            $sCssPreloads = \implode("\n", \array_map(function ($url) {
                return $this->getPreloadLink($url);
            }, $cssUrls));
            $this->appendChildToHead($sCssPreloads);
        } else {
            $cssUrls = \array_map(function ($url) {
                return $url['href'];
            }, $cssUrls);
            $this->asyncManager->loadCssAsync($cssUrls);
        }
    }
    public function appendCriticalJsToHtml($criticalJsUrl)
    {
        $this->http2Preload->add($criticalJsUrl, 'js');
        $criticalJsLink = $this->getNewJsLink($criticalJsUrl, \false, \true);
        $this->appendChildToHTML($criticalJsLink, 'head');
    }
    public function appendAsyncScriptsToHead()
    {
        if (JCH_PRO) {
            $sScript = $this->cleanScript($this->asyncManager->printHeaderScript());
            $this->appendChildToHead($sScript);
        }
    }
    /**
     *
     * @param   string  $script
     *
     * @return string
     */
    protected function cleanScript(string $script) : string
    {
        if (!Helper::isXhtml($this->oProcessor->getHtml())) {
            $script = \str_replace(array('<script type="text/javascript"><![CDATA[', '<script><![CDATA[', ']]></script>'), array('<script type="text/javascript">', '<script>', '</script>'), $script);
        }
        return $script;
    }
    public function addJsLazyLoadAssetsToHtml($id, $section)
    {
        $url = $this->buildUrl($id, 'js');
        $script = $this->getNewJsLink($url, \false, \true);
        $this->appendChildToHTML($script, $section);
    }
    public function addCssLazyLoadAssetsToHtml()
    {
        if (JCH_PRO && $this->params->get('lazyload_enable', '0')) {
            if ($this->params->get('pro_lazyload_effects', '0')) {
                $url = Paths::mediaUrl(\true) . '/core/css/ls.effects.css?' . JCH_VERSION;
                $link = $this->getNewCssLink($url);
                $this->appendChildToHead($link);
            }
            $cssNoScript = <<<HTML
<noscript>
\t\t\t<style>
\t\t\t\timg.jch-lazyload, iframe.jch-lazyload{
\t\t\t\t\tdisplay: none;
\t\t\t\t}                               
\t\t\t</style>                                
\t\t</noscript>
HTML;
            $this->appendChildToHead($cssNoScript);
        }
    }
    /**
     * Adds elements to the HTML that should be processed by Combine Js/CSS
     */
    public function preProcessHtml()
    {
        $this->addCssLazyLoadAssetsToHtml();
    }
    /**
     * @param   string  $url  Url of file
     *
     * @return string
     */
    protected function getNewCssLink(string $url) : string
    {
        //language=HTML
        return '<link rel="stylesheet" href="' . $url . '" />';
    }
    /**
     * @param   string  $url      Url of file
     * @param   bool    $isDefer  If true the 'defer attribute will be added to the script element
     * @param   bool    $isASync  If true the 'async' attribute will be added to the script element
     *
     * @return string
     */
    protected function getNewJsLink(string $url, bool $isDefer = \false, bool $isASync = \false) : string
    {
        $deferAttr = $isDefer ? $this->getFormattedHtmlAttribute('defer') : '';
        $asyncAttr = $isASync ? $this->getFormattedHtmlAttribute('async') : '';
        return '<script src="' . $url . '"' . $asyncAttr . $deferAttr . '></script>';
    }
    private function getPreloadLink(array $attr) : string
    {
        $crossorigin = !empty($attr['crossorigin']) ? ' crossorigin' : '';
        $type = !empty($attr['type']) ? ' type="' . $attr['type'] . '"' : '';
        $onload = !empty($attr['onload']) ? ' onload="' . $attr['onload'] . '"' : '';
        return "<link rel=\"preload\" href=\"{$attr['href']}\" as=\"{$attr['as']}\"{$type}{$crossorigin}{$onload} />";
    }
    /**
     * Returns HTML attribute properly formatted for XHTML/XML or HTML5
     *
     * @param   string  $attr
     *
     * @return string
     */
    private function getFormattedHtmlAttribute(string $attr) : string
    {
        return Helper::isXhtml($this->oProcessor->getHtml()) ? ' ' . $attr . '="' . $attr . '"' : ' ' . $attr;
    }
    /**
     * Generally for HTML edits that should be done last like adding preloads
     *
     * @return void
     */
    public function postProcessHtml()
    {
        if (JCH_PRO) {
            //If HttpRequests is enabled when we're using caching we need to add the preloads to the HTML rather than
            // sending  Link header
            if (Utility::isPageCacheEnabled($this->params, \true) && $this->params->get('pro_capture_cache_enable', '0') && !$this->params->get('pro_cache_platform', '0')) {
                $preloads = $this->http2Preload->getPreloads();
                foreach ($preloads as $preload) {
                    $link = $this->getPreloadLink($preload);
                    $this->prependChildToHead($link);
                }
            }
            $this->addOptimizeGFontsToHead();
            $this->addPreConnects();
        }
    }
}
