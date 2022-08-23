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

use CodeAlfa\Minify\Html;
use JchOptimize\Core\Exception\ExcludeException;
use JchOptimize\Core\FeatureHelpers\DynamicJs;
use JchOptimize\Core\FeatureHelpers\GoogleFonts;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Excludes;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Psr\Http\Client\ClientInterface;
/**
 * Handles the exclusion and replacement of files in the HTML based on set parameters
 */
class FilesManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var bool $bLoadAsync Indicates if we can load the last javascript files asynchronously
     */
    public $bLoadJsAsync = \true;
    /**
     * @var array $aCss Multidimensional array of css files to combine
     */
    public $aCss = [[]];
    /**
     * @var array $aJs Multidimensional array of js files to combine
     */
    public $aJs = [[]];
    /**
     * @var int $iIndex_js Current index of js files to be combined
     */
    public $iIndex_js = 0;
    /**
     * @var int $iIndex_css Current index of css files to be combined
     */
    public $iIndex_css = 0;
    /** @var array $aExcludedJs Javascript matches that will be excluded.
     *        Will be moved to the bottom of section if not selected in "don't move"
     */
    public $aExcludedJs = ['ieo' => [], 'peo' => []];
    /**
     * @var int $jsExcludedIndex Recorded incremented index of js files when the last file was excluded
     */
    public $jsExcludedIndex = 0;
    /**
     * @var array $defers Javascript files having the defer attribute
     */
    public $defers = [];
    /**
     * @var array $aMatch Current match being worked on
     */
    public $aMatch;
    /**
     * @var array $aExcludes Multidimensional array of excludes set in the parameters.
     */
    public $aExcludes = [];
    /**
     * @var Registry $params
     */
    private $params;
    /**
     * @var string $type Type of file being processed (css|js)
     */
    protected $type = '';
    /**
     * @var array $aMatches Array of matched elements holding links to CSS/Js files on the page
     */
    protected $aMatches = [];
    /**
     * @var int $iIndex Current index of matches
     */
    protected $iIndex = -1;
    /**
     * @var array $aReplacements Array of replacements of matched links
     */
    protected $aReplacements = [];
    /**
     * @var string $replacement String to replace the matched link
     */
    protected $replacement = '';
    /**
     * @var string $sCssExcludeType Type of exclude being processed (peo|ieo)
     */
    protected $sCssExcludeType = '';
    /**
     * @var string $sJsExcludeType Type of exclude being processed (peo|ieo)
     */
    protected $sJsExcludeType = '';
    /**
     * @var array  Array to hold files to check for duplicates
     */
    protected $aUrls = [];
    /**
     * @var ClientInterface
     */
    private $http;
    /**
     * @var Http2Preload
     */
    private $http2Preload;
    /**
     * @var FileUtils
     */
    private $fileUtils;
    /**
     * Private constructor, need to implement a singleton of this class
     */
    public function __construct(Registry $params, Http2Preload $http2Preload, FileUtils $fileUtils, ?ClientInterface $http)
    {
        $this->params = $params;
        $this->http2Preload = $http2Preload;
        $this->fileUtils = $fileUtils;
        $this->http = $http;
    }
    public function setExcludes($aExcludes)
    {
        $this->aExcludes = $aExcludes;
    }
    /**
     * @param   string  $type
     * @param   array   $aMatch
     *
     * @return string
     */
    public function processFiles(string $type, array $aMatch) : string
    {
        $this->aMatch = $aMatch;
        $this->type = $type;
        $this->iIndex++;
        $this->aMatches[$this->iIndex] = $aMatch[0];
        //Initialize replacement
        $this->replacement = '';
        try {
            if (\trim($aMatch['url']) != '') {
                $this->checkUrls($aMatch['url']);
                /**
                 * @see FilesManager::processJsUrl()
                 * @see FilesManager::processCssUrl()
                 */
                $this->{'process' . \ucfirst($type) . 'Url'}($aMatch['url']);
            } elseif (\trim($aMatch['content']) != '') {
                /**
                 * @see FilesManager::processJsContent()
                 * @see FilesManager::processCssContent()
                 */
                $this->{'process' . \ucfirst($type) . 'Content'}($aMatch['content']);
            }
        } catch (ExcludeException $e) {
        }
        return $this->replacement;
    }
    private function checkUrls(string $url)
    {
        //Exclude invalid urls
        if (!Url::isHttpScheme($url) && !Url::isDataUri($url)) {
            $this->{'exclude' . \ucfirst($this->type) . 'IEO'}();
        }
    }
    /**
     * @throws ExcludeException
     */
    private function processCssUrl(string $url)
    {
        //Get media value if attribute set
        $sMedia = $this->getMediaAttribute();
        //Remove css files
        if (Helper::findExcludes(@$this->aExcludes['remove']['css'], $url)) {
            $this->excludeCssIEO();
        }
        //process google font files
        if (JCH_PRO && $this->params->get('pro_optimize_gfont_enable', '0') && \strpos($url, 'fonts.googleapis.com') !== \false) {
            /** @see GoogleFonts::optimizeFile() */
            $this->container->get(GoogleFonts::class)->optimizeFile($url, $sMedia);
            $this->excludeCssIEO();
        }
        if ($this->isDuplicated($url)) {
            $this->excludeCssIEO();
        }
        //process excludes for css urls
        if ($this->excludeGenericUrls($url) || Helper::findExcludes(@$this->aExcludes['excludes_peo']['css'], $url)) {
            $this->excludeCssPEO();
        }
        $this->prepareCssPEO();
        $this->processSmartCombine($url);
        $this->aCss[$this->iIndex_css][] = ['url' => $url, 'media' => $sMedia];
    }
    private function getMediaAttribute() : string
    {
        $sMedia = '';
        if (\preg_match('#media=(?(?=["\'])(?:["\']([^"\']+))|(\\w+))#i', $this->aMatch[0], $aMediaTypes) > 0) {
            $sMedia .= $aMediaTypes[1] ? $aMediaTypes[1] : $aMediaTypes[2];
        }
        return $sMedia;
    }
    /**
     * @throws ExcludeException
     */
    private function excludeCssIEO()
    {
        $this->sCssExcludeType = 'ieo';
        throw new ExcludeException();
    }
    private function excludeGenericUrls(string $url) : bool
    {
        //Exclude unsupported urls
        if (Url::isDataUri($url) || !$this->isHttpAdapterAvailable($url) || Url::isSSL($url) && !\extension_loaded('openssl')) {
            return \true;
        }
        //Exclude files from external extensions if parameter not set (PEO)
        if (!$this->params->get('includeAllExtensions', '0')) {
            if (!$this->fileUtils->isInternal($url) || \preg_match('#' . Excludes::extensions() . '#i', $url)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determines if the given url requires an http wrapper to fetch it and if an http adapter is available.
     *
     * @param   string  $url
     *
     * @return bool
     */
    public function isHttpAdapterAvailable(string $url) : bool
    {
        if ($this->params->get('phpAndExternal', '0')) {
            if (\preg_match('#^(?:http|//)#i', $url) && !$this->fileUtils->isInternal($url) || $this->isPHPFile($url)) {
                if (\is_null($this->http)) {
                    return \false;
                } else {
                    return \true;
                }
            } else {
                return \true;
            }
        } else {
            return !(\preg_match('#^(?:http|//)#i', $url) && !$this->fileUtils->isInternal($url) || $this->isPHPFile($url));
        }
    }
    public function isPHPFile(string $url) : bool
    {
        return \preg_match('#\\.php|^(?![^?\\#]*\\.(?:css|js|png|jpe?g|gif|bmp)(?:[?\\#]|$)).++#i', $url);
    }
    /**
     * @throws ExcludeException
     */
    private function excludeCssPEO()
    {
        //if previous file was excluded increment css index
        if (!empty($this->aCss[$this->iIndex_css]) && !$this->params->get('optimizeCssDelivery_enable', '0')) {
            $this->iIndex_css++;
        }
        //Just return the match at same location
        $this->replacement = $this->aMatch[0];
        $this->sCssExcludeType = 'peo';
        throw new ExcludeException();
    }
    /**
     * Checks if a file appears more than once on the page so that it's not duplicated in the combined files
     *
     * @param   string  $url  Url of file
     *
     * @return bool        True if already included
     * @since
     */
    public function isDuplicated(string $url) : bool
    {
        $url = (new Uri($url))->toString(['host', 'path', 'query']);
        $return = \in_array($url, $this->aUrls);
        if (!$return) {
            $this->aUrls[] = $url;
        }
        return $return;
    }
    private function prepareCssPEO()
    {
        //return marker for combined file
        if (empty($this->aCss[$this->iIndex_css]) && !$this->params->get('optimizeCssDelivery_enable', '0')) {
            $this->replacement = '<JCH_CSS' . $this->iIndex_css . '>';
        }
    }
    private function processSmartCombine(string $url)
    {
        if ($this->params->get('pro_smart_combine', '0')) {
            $sType = $this->type;
            $aSmartCombineValues = $this->params->get('pro_smart_combine_values', '');
            $aSmartCombineValues = $aSmartCombineValues != '' ? \json_decode(\rawurldecode($aSmartCombineValues)) : [];
            //Index of files currently being smart combined
            static $iSmartCombineIndex_js = \false;
            static $iSmartCombineIndex_css = \false;
            $sBaseUrl = \preg_replace('#[?\\#].*+#i', '', $url);
            foreach (Excludes::smartCombine() as $iIndex => $sRegex) {
                if (\preg_match('#' . $sRegex . '#i', $url) && \in_array($sBaseUrl, $aSmartCombineValues)) {
                    //We're in a batch
                    //Is this the first file in this batch?
                    if (!empty($this->{'a' . \ucfirst($sType)}[$this->{'iIndex_' . $sType}]) && ${'iSmartCombineIndex_' . $sType} !== $iIndex) {
                        $this->{'iIndex_' . $sType}++;
                        if ($sType == 'css' && $this->replacement == '' && !$this->params->get('optimizeCssDelivery_enable', '0')) {
                            $this->replacement = '<JCH_CSS' . $this->iIndex_css . '>';
                        }
                    }
                    if ($sType == 'js') {
                        $this->bLoadJsAsync = \false;
                    }
                    //Save index
                    ${'iSmartCombineIndex_' . $sType} = $iIndex;
                    break;
                } else {
                    //Have we just finished a batch?
                    if (${'iSmartCombineIndex_' . $sType} === $iIndex) {
                        ${'iSmartCombineIndex_' . $sType} = \false;
                        if (!empty($this->{'a' . \ucfirst($sType)}[$this->{'iIndex_' . $sType}])) {
                            $this->{'iIndex_' . $sType}++;
                            if ($sType == 'css' && $this->replacement == '' && !$this->params->get('optimizeCssDelivery_enable', '0')) {
                                $this->replacement = '<JCH_CSS' . $this->iIndex_css . '>';
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * @throws ExcludeException
     */
    private function processCssContent(string $content)
    {
        $media = $this->getMediaAttribute();
        if (Helper::findExcludes(@$this->aExcludes['excludes_peo']['css_script'], $content, 'css') || !$this->params->get('inlineStyle', '0') || $this->params->get('excludeAllStyles', '0')) {
            $this->excludeCssPEO();
        }
        $this->prepareCssPEO();
        $this->aCss[$this->iIndex_css][] = ['content' => Html::cleanScript($content, 'css'), 'media' => $media];
    }
    /**
     * @throws ExcludeException
     */
    private function processJsUrl(string $url)
    {
        //Remove js files selected as critical
        if (JCH_PRO) {
            /** @see DynamicJs::handleCriticalUrls() */
            $this->container->get(DynamicJs::class)->handleCriticalUrls($url);
        }
        //Remove js files
        if (Helper::findExcludes(@$this->aExcludes['remove']['js'], $url)) {
            $this->excludeJsIEO();
        }
        if ($this->isDuplicated($url)) {
            $this->excludeJsIEO();
        }
        //Process IEO Excludes for js urls
        if (Helper::findExcludes(@$this->aExcludes['excludes_ieo']['js'], $url)) {
            //Push excluded files
            $deferred = $this->isFileDeferred($this->aMatch[0]);
            $this->http2Preload->add($url, 'js', $deferred);
            //Return match if selected as 'don't move'
            if (Helper::findExcludes(@$this->aExcludes['dontmove']['js'], $url)) {
                $this->replacement = $this->aMatch[0];
            } else {
                $this->aExcludedJs['ieo'][] = $this->aMatch[0];
            }
            $this->excludeJsIEO();
        }
        //Remove modules and files with nomodule
        if (\preg_match('#type\\s*=\\s*[\'"]?module|nomodule#i', $this->aMatch[0])) {
            //Just add to array of files to be loaded at bottom of section
            $this->defers['matches'][] = $this->aMatch[0];
            $this->bLoadJsAsync = \false;
            $this->excludeJsIEO();
        }
        if ($this->isFileDeferred($this->aMatch[0], \true)) {
            if ($this->params->get('pro_remove_unused_js_enable', '0')) {
                $this->defers[] = ['url' => $url];
            } else {
                $this->defers['matches'][] = $this->aMatch[0];
            }
            //We now have to defer the last js file
            $this->bLoadJsAsync = \false;
            $this->excludeJsIEO();
        }
        //Exclude js files PEO
        if ($this->excludeGenericUrls($url) || Helper::findExcludes(@$this->aExcludes['excludes_peo']['js'], $url)) {
            //push excluded file
            $this->http2Preload->add($url, 'js');
            //prepare js match for excluding PEO
            $this->prepareJsPEO();
            //Return match if selected as "don't move"
            if (Helper::findExcludes(@$this->aExcludes['dontmove']['js'], $url)) {
                //Need to make sure execution order is maintained
                $this->prepareJsDontMoveReplacement();
            } else {
                $this->aExcludedJs['peo'][] = $this->aMatch[0];
            }
            $this->excludeJsPEO();
        }
        $this->processSmartCombine($url);
        $this->aJs[$this->iIndex_js][] = ['url' => $url];
    }
    public function excludeJsIEO()
    {
        $this->sJsExcludeType = 'ieo';
        throw new ExcludeException();
    }
    public function isFileDeferred($sScriptTag, $bIgnoreAsync = \false)
    {
        $a = \JchOptimize\Core\Html\Parser::HTML_ATTRIBUTE_CP();
        //Shall we ignore files that also include the async attribute
        if ($bIgnoreAsync) {
            $exclude = "(?!(?>\\s*+{$a})*?\\s*+async\\b)";
            $attr = 'defer';
        } else {
            $exclude = '';
            $attr = '(?:defer|async)';
        }
        return \preg_match("#<\\w++\\b{$exclude}(?>\\s*+{$a})*?\\s*+{$attr}\\b#i", $sScriptTag);
    }
    private function prepareJsPEO()
    {
        //If files were previously added for combine in the current index
        // then place marker for combined file(s) above match marked for exclude
        if (!empty($this->aJs[$this->iIndex_js])) {
            $jsReturn = '';
            for ($i = $this->jsExcludedIndex; $i <= $this->iIndex_js; $i++) {
                $jsReturn .= '<JCH_JS' . $i . '>' . "\n\t";
            }
            $this->aMatch[0] = $jsReturn . $this->aMatch[0];
            //increment index of combined files and record it
            $this->jsExcludedIndex = ++$this->iIndex_js;
        }
    }
    private function prepareJsDontMoveReplacement()
    {
        //We'll need to put all the PEO excluded files above this one
        $this->aMatch[0] = \implode("\n", $this->aExcludedJs['peo']) . "\n" . $this->aMatch[0];
        $this->replacement = $this->aMatch[0];
        //reinitialize array of PEO excludes
        $this->aExcludedJs['peo'] = [];
    }
    /**
     * @throws ExcludeException
     */
    private function excludeJsPEO()
    {
        //Can no longer load last combined file asynchronously
        $this->bLoadJsAsync = \false;
        $this->sJsExcludeType = 'peo';
        throw new ExcludeException();
    }
    /**
     * @throws ExcludeException
     */
    private function processJsContent($content)
    {
        //Remove js files selected as critical
        if (JCH_PRO) {
            /** @see DynamicJs::handleCriticalScripts() */
            $this->container->get(DynamicJs::class)->handleCriticalScripts($content);
        }
        //process IEO excludes for js scripts
        if (Helper::findExcludes(@$this->aExcludes['excludes_ieo']['js_script'], $content, 'js')) {
            //Return match if selected as "don't move"
            if (Helper::findExcludes(@$this->aExcludes['dontmove']['scripts'], $content, 'js')) {
                $this->replacement = $this->aMatch[0];
            } else {
                $this->aExcludedJs['ieo'][] = $this->aMatch[0];
            }
            $this->excludeJsIEO();
        }
        //process PEO excludes for js scripts
        if (Helper::findExcludes(@$this->aExcludes['excludes_peo']['js_script'], $content, 'js') || !$this->params->get('inlineScripts', '0') || $this->params->get('excludeAllScripts', '0')) {
            //prepare js match for excluding PEO
            $this->prepareJsPEO();
            //Return match is selected as don't move
            if (Helper::findExcludes(@$this->aExcludes['dontmove']['scripts'], $content, 'js')) {
                //Need to make sure execution order is maintained
                $this->prepareJsDontMoveReplacement();
            } else {
                $this->aExcludedJs['peo'][] = $this->aMatch[0];
            }
            $this->excludeJsPEO();
        }
        $this->aJs[$this->iIndex_js][] = ['content' => Html::cleanScript($content, 'js')];
    }
}
