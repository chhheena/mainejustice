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
namespace JchOptimize\Core\Html\Callbacks;

\defined('_JCH_EXEC') or die('Restricted access');
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Excludes;
use Joomla\Registry\Registry;
class CombineJsCss extends \JchOptimize\Core\Html\Callbacks\AbstractCallback
{
    /**
     * @var array          Array of excludes parameters
     */
    private $excludes;
    /**
     * @var string        Section of the HTML being processed
     */
    private $section = 'head';
    /**
     * @var FilesManager
     */
    private $filesManager;
    /**
     * @var Http2Preload
     */
    private $http2Preload;
    /**
     * @var HtmlProcessor
     */
    private $htmlProcessor;
    /**
     * CombineJsCss constructor.
     */
    public function __construct(Registry $params, FilesManager $filesManager, Http2Preload $http2Preload, HtmlProcessor $htmlProcessor)
    {
        parent::__construct($params);
        $this->filesManager = $filesManager;
        $this->http2Preload = $http2Preload;
        $this->htmlProcessor = $htmlProcessor;
        $this->setupExcludes();
    }
    /**
     * Retrieves all exclusion parameters for the Combine Files feature
     *
     * @return void
     */
    private function setupExcludes()
    {
        JCH_DEBUG ? Profiler::start('SetUpExcludes') : null;
        $this->excludes = array();
        $aExcludes = array();
        $oParams = $this->params;
        //These parameters will be excluded while preserving execution order
        $aExJsComp = $this->getExComp($oParams->get('excludeJsComponents_peo', ''));
        $aExCssComp = $this->getExComp($oParams->get('excludeCssComponents', ''));
        $aExcludeJs_peo = Helper::getArray($oParams->get('excludeJs_peo', ''));
        $aExcludeCss_peo = Helper::getArray($oParams->get('excludeCss', ''));
        $aExcludeScript_peo = Helper::getArray($oParams->get('excludeScripts_peo', ''));
        $aExcludeStyle_peo = Helper::getArray($oParams->get('excludeStyles', ''));
        $aExcludeScript_peo = \array_map(function ($sScript) {
            return \stripslashes($sScript);
        }, $aExcludeScript_peo);
        $this->excludes['excludes_peo']['js'] = \array_merge($aExcludeJs_peo, $aExJsComp, array('.com/maps/api/js', '.com/jsapi', '.com/uds', 'typekit.net', 'cdn.ampproject.org', 'googleadservices.com/pagead/conversion'), Excludes::head('js'));
        $this->excludes['excludes_peo']['css'] = \array_merge($aExcludeCss_peo, $aExCssComp, Excludes::head('css'));
        $this->excludes['excludes_peo']['js_script'] = $aExcludeScript_peo;
        $this->excludes['excludes_peo']['css_script'] = $aExcludeStyle_peo;
        $this->excludes['critical_js']['js'] = Helper::getArray($oParams->get('pro_criticalJs', ''));
        $this->excludes['critical_js']['script'] = Helper::getArray($oParams->get('pro_criticalScripts', ''));
        //These parameters will be excluded without preserving execution order
        $aExJsComp_ieo = $this->getExComp($oParams->get('excludeJsComponents', ''));
        $aExcludeJs_ieo = Helper::getArray($oParams->get('excludeJs', ''));
        $aExcludeScript_ieo = Helper::getArray($oParams->get('excludeScripts', ''));
        $this->excludes['excludes_ieo']['js'] = \array_merge($aExcludeJs_ieo, $aExJsComp_ieo);
        $this->excludes['excludes_ieo']['js_script'] = $aExcludeScript_ieo;
        $this->excludes['dontmove']['js'] = Helper::getArray($oParams->get('dontmoveJs', ''));
        $this->excludes['dontmove']['scripts'] = Helper::getArray($oParams->get('dontmoveScripts', ''));
        $this->excludes['remove']['js'] = Helper::getArray($oParams->get('remove_js', ''));
        $this->excludes['remove']['css'] = Helper::getArray($oParams->get('remove_css', ''));
        $aExcludes['head'] = $this->excludes;
        if ($this->params->get('bottom_js', '0') == 1) {
            $this->excludes['excludes_peo']['js_script'] = \array_merge($this->excludes['excludes_peo']['js_script'], array('.write(', 'var google_conversion'), Excludes::body('js', 'script'));
            $this->excludes['excludes_peo']['js'] = \array_merge($this->excludes['excludes_peo']['js'], array('.com/recaptcha/api'), Excludes::body('js'));
            $this->excludes['dontmove']['scripts'] = \array_merge($this->excludes['dontmove']['scripts'], array('.write('));
            $aExcludes['body'] = $this->excludes;
        }
        JCH_DEBUG ? Profiler::stop('SetUpExcludes', \true) : null;
        $this->excludes = $aExcludes;
    }
    /**
     * Generates regex for excluding components set in plugin params
     *
     * @param $sExComParam
     *
     * @return array
     */
    private function getExComp($sExComParam) : array
    {
        $aComponents = Helper::getArray($sExComParam);
        $aExComp = array();
        if (!empty($aComponents)) {
            $aExComp = \array_map(function ($sValue) {
                return $sValue . '/';
            }, $aComponents);
        }
        return $aExComp;
    }
    /**
     * Callback function used to remove urls of css and js files in head tags
     *
     * @param   array  $matches  Array of all matches
     *
     * @return string               Returns the url if excluded, empty string otherwise
     */
    public function processMatches($matches) : string
    {
        if (empty($matches[0])) {
            return $matches[0];
        }
        $url = $matches['url'] = \trim($matches[4] ?? '');
        $content = $matches['content'] = !isset($matches[4]) ? $matches[2] : '';
        if (\preg_match('#^<!--#', $matches[0])) {
            return $matches[0];
        }
        //If url is invalid just remove it, sometimes they cause the page to download again so most likely
        //would be better
        if (Url::isInvalid($url) && \trim($content) == '') {
            return '';
        }
        $sType = \strcasecmp($matches[1], 'script') == 0 ? 'js' : 'css';
        if ($sType == 'js' && (!$this->params->get('javascript', '1') || !$this->htmlProcessor->isCombineFilesSet())) {
            $deferred = $this->filesManager->isFileDeferred($matches[0]);
            $this->http2Preload->add($url, 'script', $deferred);
            return $matches[0];
        }
        if ($sType == 'css' && (!$this->params->get('css', '1') || !$this->htmlProcessor->isCombineFilesSet())) {
            $this->http2Preload->add($url, 'style');
            return $matches[0];
        }
        $this->filesManager->setExcludes($this->excludes[$this->section]);
        return $this->filesManager->processFiles($sType, $matches);
    }
    public function setSection($section)
    {
        $this->section = $section;
    }
}
