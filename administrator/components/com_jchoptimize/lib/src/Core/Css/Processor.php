<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\Css;

\defined('_JCH_EXEC') or die('Restricted access');
use CodeAlfa\RegexTokenizer\Debug\Debug;
use JchOptimize\Core\Css\Callbacks\CombineMediaQueries;
use JchOptimize\Core\Css\Callbacks\CorrectUrls;
use JchOptimize\Core\Css\Callbacks\ExtractCriticalCss;
use JchOptimize\Core\Css\Callbacks\FormatCss;
use JchOptimize\Core\Css\Callbacks\HandleAtRules;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FileInfosUtilsTrait;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\SerializableTrait;
use JchOptimize\Platform\Profiler;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Serializable;
class Processor implements LoggerAwareInterface, ContainerAwareInterface, Serializable
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use Debug;
    use FileInfosUtilsTrait;
    use SerializableTrait;
    protected $css;
    /**
     * @var Registry
     */
    private $params;
    /**
     * @var string
     */
    private $debugUrl;
    /**
     * @var CombineMediaQueries
     */
    private $combineMediaQueries;
    /**
     * @var CorrectUrls
     */
    private $correctUrls;
    /**
     * @var ExtractCriticalCss
     */
    private $extractCriticalCss;
    /**
     * @var FormatCss
     */
    private $formatCss;
    /**
     * @var HandleAtRules
     */
    private $handleAtRules;
    public function __construct(Registry $params, CombineMediaQueries $combineMediaQueries, CorrectUrls $correctUrls, ExtractCriticalCss $extractCriticalCss, FormatCss $formatCss, HandleAtRules $handleAtRules)
    {
        $this->params = $params;
        $this->combineMediaQueries = $combineMediaQueries;
        $this->correctUrls = $correctUrls;
        $this->extractCriticalCss = $extractCriticalCss;
        $this->formatCss = $formatCss;
        $this->handleAtRules = $handleAtRules;
    }
    public function setCssInfos($cssInfos)
    {
        $this->combineMediaQueries->setCssInfos($cssInfos);
        $this->correctUrls->setCssInfos($cssInfos);
        $this->handleAtRules->setCssInfos($cssInfos);
        $this->fileUtils = $this->container->get(FileUtils::class);
        $this->debugUrl = $this->prepareFileUrl($cssInfos, 'css');
        //initialize debug
        $this->_debug($this->debugUrl, '', 'CssProcessorConstructor');
    }
    public function getCss()
    {
        return $this->css;
    }
    public function setCss($css)
    {
        if (\function_exists('mb_convert_encoding')) {
            $sEncoding = \mb_detect_encoding($css);
            if ($sEncoding === \false) {
                $sEncoding = \mb_internal_encoding();
            }
            $css = \mb_convert_encoding($css, 'utf-8', $sEncoding);
        }
        $this->css = $css;
    }
    public function formatCss()
    {
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oParser->setExcludes(array(\JchOptimize\Core\Css\Parser::BLOCK_COMMENT(), \JchOptimize\Core\Css\Parser::LINE_COMMENT(), \JchOptimize\Core\Css\Parser::CSS_NESTED_AT_RULES_CP()));
        $sPrepareExcludeRegex = '\\|"(?>[^"{}]*+"?)*?[^"{}]*+"\\|';
        $oSearchObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oSearchObject->setCssNestedRuleName('media', \true);
        $oSearchObject->setCssNestedRuleName('supports', \true);
        $oSearchObject->setCssNestedRuleName('document', \true);
        $oSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_RULES());
        $oSearchObject->setCssRuleCriteria('*');
        $oSearchObject->setCssCustomRule($sPrepareExcludeRegex);
        $oSearchObject->setCssCustomRule(\JchOptimize\Core\Css\Parser::CSS_INVALID_CSS());
        $oParser->setCssSearchObject($oSearchObject);
        $oParser->disableBranchReset();
        $this->formatCss->validCssRules = $sPrepareExcludeRegex;
        try {
            $this->css = $oParser->processMatchesWithCallback($this->css . '}', $this->formatCss);
        } catch (Exception\PregErrorException $oException) {
            $this->logger->error('FormatCss failed - ' . $this->debugUrl . ': ' . $oException->getMessage());
        }
        $this->_debug($this->debugUrl, '', 'formatCss');
    }
    public function preloadHttp2($css, $isFontsOnly = \false)
    {
        $this->css = $css;
        $this->processUrls(\true, $isFontsOnly);
    }
    public function processUrls($isHttp2 = \false, $isFontsOnly = \false, $isBackend = \false)
    {
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oSearchObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oSearchObject->setCssNestedRuleName('font-face');
        $oSearchObject->setCssNestedRuleName('media', \true);
        $oSearchObject->setCssNestedRuleName('supports', \true);
        $oSearchObject->setCssNestedRuleName('document', \true);
        $oSearchObject->setCssRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_URL_CP());
        $oSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_IMPORT_CP());
        $oParser->setCssSearchObject($oSearchObject);
        $this->correctUrls->isHttp2 = $isHttp2;
        $this->correctUrls->isFontsOnly = $isFontsOnly;
        $this->correctUrls->isBackend = $isBackend;
        try {
            $this->css = $oParser->processMatchesWithCallback($this->css, $this->correctUrls);
        } catch (Exception\PregErrorException $oException) {
            $sPreMessage = $isHttp2 ? 'Http/2 preload failed' : 'ProcessUrls failed';
            $this->logger->error($sPreMessage . ' - ' . $this->debugUrl . ': ' . $oException->getMessage());
        }
        $this->_debug($this->debugUrl, '', 'processUrls');
    }
    public function processAtRules()
    {
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oSearchObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_IMPORT_CP(\true));
        $oSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_CHARSET_CP());
        $oSearchObject->setCssNestedRuleName('font-face');
        $oSearchObject->setCssNestedRuleName('media', \true);
        $oParser->setCssSearchObject($oSearchObject);
        try {
            $this->css = $this->cleanEmptyMedias($oParser->processMatchesWithCallback($this->css, $this->handleAtRules));
        } catch (Exception\PregErrorException $oException) {
            $this->logger->error('ProcessAtRules failed - ' . $this->debugUrl . ': ' . $oException->getMessage());
        }
        $this->_debug($this->debugUrl, '', 'ProcessAtRules');
    }
    public function cleanEmptyMedias($css)
    {
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oParser->setExcludes(array(\JchOptimize\Core\Css\Parser::BLOCK_COMMENT(), '[@/]'));
        $oParser->setParseTerm('[^@/]*+');
        $oCssEmptyMediaObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oCssEmptyMediaObject->setCssNestedRuleName('media', \false, \true);
        $oParser->setCssSearchObject($oCssEmptyMediaObject);
        return $oParser->replaceMatches($css, '');
    }
    public function processMediaQueries()
    {
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oSearchObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oSearchObject->setCssNestedRuleName('media');
        $oSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_IMPORT_CP(\true));
        $oSearchObject->setCssRuleCriteria('*');
        $oParser->setCssSearchObject($oSearchObject);
        $oParser->disableBranchReset();
        try {
            $this->css = $oParser->processMatchesWithCallback($this->css, $this->combineMediaQueries);
        } catch (Exception\PregErrorException $oException) {
            $this->logger->error('HandleMediaQueries failed - ' . $this->debugUrl . ': ' . $oException->getMessage());
        }
        $this->_debug($this->debugUrl, '', 'handleMediaQueries');
    }
    public function optimizeCssDelivery($css, $html) : string
    {
        JCH_DEBUG ? Profiler::start('OptimizeCssDelivery') : null;
        $this->_debug('', '', 'StartCssDelivery');
        //Place space around HTML attributes for easy processing with XPath
        $html = \preg_replace('#\\s*=\\s*["\']([^"\']++)["\']#i', '=" $1 "', $html);
        //Truncate HTML to number of elements set in params
        $sHtmlAboveFold = '';
        \preg_replace_callback('#<(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+(<ul\\b[^>]*+>(?>[^<]*+<(?!ul)[^<]*+|(?1))*?</ul>)?)*?(?=<[a-z0-9])#i', function ($aM) use(&$sHtmlAboveFold) {
            $sHtmlAboveFold .= $aM[0];
            return;
        }, $html, (int) $this->params->get('optimizeCssDelivery', '800'));
        $this->_debug('', '', 'afterHtmlTruncated');
        $oDom = new \DOMDocument();
        //Load HTML in DOM
        \libxml_use_internal_errors(\true);
        $oDom->loadHtml($sHtmlAboveFold);
        \libxml_clear_errors();
        $oXPath = new \DOMXPath($oDom);
        $this->_debug('', '', 'afterLoadHtmlDom');
        $sFullHtml = $html;
        $oParser = new \JchOptimize\Core\Css\Parser();
        $oCssSearchObject = new \JchOptimize\Core\Css\CssSearchObject();
        $oCssSearchObject->setCssNestedRuleName('media', \true);
        $oCssSearchObject->setCssNestedRuleName('supports', \true);
        $oCssSearchObject->setCssNestedRuleName('document', \true);
        $oCssSearchObject->setCssNestedRuleName('font-face');
        $oCssSearchObject->setCssNestedRuleName('keyframes');
        $oCssSearchObject->setCssNestedRuleName('page');
        $oCssSearchObject->setCssNestedRuleName('font-feature-values');
        $oCssSearchObject->setCssNestedRuleName('counter-style');
        $oCssSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_IMPORT_CP());
        $oCssSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_CHARSET_CP());
        $oCssSearchObject->setCssAtRuleCriteria(\JchOptimize\Core\Css\Parser::CSS_AT_NAMESPACE());
        $oCssSearchObject->setCssRuleCriteria('.');
        $this->extractCriticalCss->sHtmlAboveFold = $sHtmlAboveFold;
        $this->extractCriticalCss->sFullHtml = $sFullHtml;
        $this->extractCriticalCss->oXPath = $oXPath;
        $oParser->setCssSearchObject($oCssSearchObject);
        $sCriticalCss = $oParser->processMatchesWithCallback($css, $this->extractCriticalCss);
        $sCriticalCss = $this->cleanEmptyMedias($sCriticalCss);
        //Process Font-Face and Key frames
        $this->extractCriticalCss->isPostProcessing = \true;
        $sPostCss = $oParser->processMatchesWithCallback($this->extractCriticalCss->postCss, $this->extractCriticalCss);
        JCH_DEBUG ? Profiler::stop('OptimizeCssDelivery', \true) : null;
        return $sCriticalCss . $sPostCss;
        //$this->_debug(self::cssRulesRegex(), '', 'afterCleanCriticalCss');
    }
    public function getImports() : string
    {
        return \implode($this->handleAtRules->getImports());
    }
    public function getImages() : array
    {
        return $this->correctUrls->getImages();
    }
    public function getFontFace() : string
    {
        return $this->handleAtRules->getFontFace();
    }
}
