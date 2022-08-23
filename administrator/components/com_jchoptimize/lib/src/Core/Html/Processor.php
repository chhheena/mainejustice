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
use JchOptimize\Core\Cdn as CdnCore;
use JchOptimize\Core\Css\Parser as CssParser;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FeatureHelpers\LazyLoadExtended;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Callbacks\Cdn as CdnCallback;
use JchOptimize\Core\Html\Callbacks\CombineJsCss;
use JchOptimize\Core\Html\Callbacks\LazyLoad;
use JchOptimize\Core\SystemUri;
use JchOptimize\Platform\Profiler;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
/**
 * Class Processor
 * @package JchOptimize\Core\Html
 *
 * This class interacts with the Parser passing over HTML elements, criteria and callbacks to parse for in the HTML
 * and maintains the processed HTML
 */
class Processor implements LoggerAwareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    /**
     * @var bool           Indicates if the page is an Amp page
     */
    public $isAmpPage = \false;
    /**
     * @var array $images Array of IMG elements requiring width/height attribute
     */
    public $images = [];
    /**
     * @var Registry       Plugin parameters
     */
    private $params;
    /**
     * @var string         Used to determine the end of useful string after parsing
     */
    private $sRegexMarker = 'JCHREGEXMARKER';
    /**
     * @var string         HTML being processed
     */
    private $html;
    /**
     * Processor constructor.
     *
     * @param   Registry  $oParams  Plugin parameters
     */
    public function __construct(Registry $oParams)
    {
        $this->params = $oParams;
    }
    /**
     * Returns the HTML being processed
     */
    public function getHtml() : string
    {
        return $this->html;
    }
    public function setHtml($html)
    {
        $this->html = $html;
        $this->isAmpPage = (bool) \preg_match('#<html [^>]*?(?:&\\#26A1;|\\bamp\\b)#i', $html);
        //Disable these features on Amp pages since no custom javascript allowed
        if ($this->params->get('ampPage', '0')) {
            $this->params->set('pro_remove_unused_css', '0');
            $this->params->set('javascript', '0');
            $this->params->set('css', '0');
            $this->params->set('pro_reduce_dom', '0');
            $this->params->set('lazyload_enable', '0');
            $this->params->set('pro_remove_unused_js', '0');
        }
    }
    public function processCombineJsCss()
    {
        if ($this->isCombineFilesSet() || $this->params->get('pro_http2_push_enable', '0')) {
            try {
                $oParser = new \JchOptimize\Core\Html\Parser();
                $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
                $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('noscript'));
                $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('template'));
                $this->setUpJsCssCriteria($oParser);
                $combineJsCss = $this->container->get(CombineJsCss::class);
                $combineJsCss->setSection('head');
                $sProcessedHeadHtml = $oParser->processMatchesWithCallback($this->getHeadHtml(), $combineJsCss);
                $this->setHeadHtml($sProcessedHeadHtml);
                if ($this->params->get('bottom_js', '0')) {
                    $combineJsCss->setSection('body');
                    $sProcessedBodyHtml = $oParser->processMatchesWithCallback($this->getBodyHtml(), $combineJsCss);
                    $this->setBodyHtml($sProcessedBodyHtml);
                }
            } catch (Exception\ExceptionInterface $oException) {
                $this->logger->error('CombineJsCss failed ' . $oException->getMessage());
            }
        }
    }
    public function isCombineFilesSet() : bool
    {
        return !Helper::isMsieLT10() && $this->params->get('combine_files_enable', '1');
    }
    protected function setUpJsCssCriteria(\JchOptimize\Core\Html\Parser $oParser)
    {
        $oJsFilesElement = new \JchOptimize\Core\Html\ElementObject();
        $oJsFilesElement->setNamesArray(array('script'));
        //language=RegExp
        $oJsFilesElement->addNegAttrCriteriaRegex('type==(?!(?>[\'"]?)(?:(?:text|application)/javascript|module)[\'"> ])');
        $oJsFilesElement->setCaptureAttributesArray(array('src'));
        $oJsFilesElement->setValueCriteriaRegex('(?=.)');
        $oParser->addElementObject($oJsFilesElement);
        $oJsContentElement = new \JchOptimize\Core\Html\ElementObject();
        $oJsContentElement->setNamesArray(array('script'));
        //language=RegExp
        $oJsContentElement->addNegAttrCriteriaRegex('src|type==(?!(?>[\'"]?)(?:text|application)/javascript[\'"> ])');
        $oJsContentElement->bCaptureContent = \true;
        $oParser->addElementObject($oJsContentElement);
        $oCssFileElement = new \JchOptimize\Core\Html\ElementObject();
        $oCssFileElement->bSelfClosing = \true;
        $oCssFileElement->setNamesArray(array('link'));
        //language=RegExp
        $oCssFileElement->addNegAttrCriteriaRegex('itemprop|disabled|type==(?!(?>[\'"]?)text/css[\'"> ])|rel==(?!(?>[\'"]?)stylesheet[\'"> ])');
        $oCssFileElement->setCaptureAttributesArray(array('href'));
        $oCssFileElement->setValueCriteriaRegex('(?=.)');
        $oParser->addElementObject($oCssFileElement);
        $oStyleElement = new \JchOptimize\Core\Html\ElementObject();
        $oStyleElement->setNamesArray(array('style'));
        //language=RegExp
        $oStyleElement->addNegAttrCriteriaRegex('scope|amp|type==(?!(?>[\'"]?)text/(?:css|stylesheet)[\'"> ])');
        $oStyleElement->bCaptureContent = \true;
        $oParser->addElementObject($oStyleElement);
    }
    public function getHeadHtml() : string
    {
        \preg_match('#' . \JchOptimize\Core\Html\Parser::HTML_HEAD_ELEMENT() . '#i', $this->html, $aMatches);
        return $aMatches[0] . $this->sRegexMarker;
    }
    public function setHeadHtml($sHtml)
    {
        $sHtml = $this->cleanRegexMarker($sHtml);
        $this->html = \preg_replace('#' . \JchOptimize\Core\Html\Parser::HTML_HEAD_ELEMENT() . '#i', Helper::cleanReplacement($sHtml), $this->html, 1);
    }
    protected function cleanRegexMarker($sHtml)
    {
        return \preg_replace('#' . \preg_quote($this->sRegexMarker, '#') . '.*+$#', '', $sHtml);
    }
    public function getBodyHtml()
    {
        \preg_match('#' . \JchOptimize\Core\Html\Parser::HTML_BODY_ELEMENT() . '#si', $this->html, $aMatches);
        return $aMatches[0] . $this->sRegexMarker;
    }
    public function setBodyHtml($sHtml)
    {
        $sHtml = $this->cleanRegexMarker($sHtml);
        $this->html = \preg_replace('#' . \JchOptimize\Core\Html\Parser::HTML_BODY_ELEMENT() . '#si', Helper::cleanReplacement($sHtml), $this->html, 1);
    }
    /**
     * @return array|mixed
     */
    public function processImagesForApi()
    {
        try {
            $oParser = new \JchOptimize\Core\Html\Parser();
            $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
            $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENTS(array('script', 'noscript', 'style')));
            $oImgElement = new \JchOptimize\Core\Html\ElementObject();
            $oImgElement->bSelfClosing = \true;
            $oImgElement->setNamesArray(array('img'));
            $oImgElement->setCaptureAttributesArray(array('src', 'srcset'));
            $oParser->addElementObject($oImgElement);
            unset($oImgElement);
            $oBgElement = new \JchOptimize\Core\Html\ElementObject();
            $oBgElement->setNamesArray(array('[^\\s/"\'=<>]++'));
            $oBgElement->bSelfClosing = \true;
            $oBgElement->setCaptureAttributesArray(array('style'));
            //language=RegExp
            $sValueCriteriaRegex = '(?=(?>[^b>]*+b?)*?[^b>]*+(background(?:-image)?))' . '(?=(?>[^u>]*+u?)*?[^u>]*+(' . CssParser::CSS_URL_CP(\true) . '))';
            $oBgElement->setValueCriteriaRegex(array('style' => $sValueCriteriaRegex));
            $oParser->addElementObject($oBgElement);
            unset($oBgElement);
            return $oParser->findMatches($this->getBodyHtml(), \PREG_SET_ORDER);
        } catch (Exception\PregErrorException $oException) {
            $this->logger->error('ProcessApiImages failed ' . $oException->getMessage());
        }
    }
    public function processLazyLoad()
    {
        $bLazyLoad = (bool) $this->params->get('lazyload_enable', '0');
        if ($bLazyLoad || $this->params->get('pro_http2_push_enable', '0') || $this->params->get('pro_next_gen_images', '1')) {
            JCH_DEBUG ? Profiler::start('LazyLoadImages') : null;
            $sHtml = '<JCH_START>' . $this->getBodyHtml();
            \preg_match('#(^(?:(?:<[0-9a-z]++[^>]*+>[^<]*+(?><[^0-9a-z][^<]*+)*+){0,81}))(.*+)#six', $sHtml, $aMatches);
            $sAboveFoldHtml = \str_replace('<JCH_START>', '', $aMatches[1]);
            $sBelowFoldHtml = $aMatches[2];
            try {
                $http2Args = ['lazyload' => \false, 'deferred' => \false, 'parent' => ''];
                $oAboveFoldParser = new \JchOptimize\Core\Html\Parser();
                //language=RegExp
                $this->setupLazyLoadCriteria($oAboveFoldParser, \false);
                /** @var LazyLoad $http2Callback */
                $http2Callback = $this->container->get(LazyLoad::class);
                $http2Callback->setLazyLoadArgs($http2Args);
                $processedAboveFoldHtml = $oAboveFoldParser->processMatchesWithCallback($sAboveFoldHtml, $http2Callback);
                $oBelowFoldParser = new \JchOptimize\Core\Html\Parser();
                $lazyLoadArgs = ['lazyload' => $bLazyLoad, 'deferred' => \true, 'parent' => ''];
                $this->setupLazyLoadCriteria($oBelowFoldParser, \true);
                /** @var LazyLoad $lazyLoadCallback */
                $lazyLoadCallback = $this->container->get(LazyLoad::class);
                $lazyLoadCallback->setLazyLoadArgs($lazyLoadArgs);
                $processedBelowFoldHtml = $oBelowFoldParser->processMatchesWithCallback($sBelowFoldHtml, $lazyLoadCallback);
                $this->setBodyHtml($processedAboveFoldHtml . $processedBelowFoldHtml);
            } catch (Exception\PregErrorException $oException) {
                $this->logger->error('Lazy-load failed: ' . $oException->getMessage());
            }
            JCH_DEBUG ? Profiler::stop('LazyLoadImages', \true) : null;
        }
    }
    protected function setupLazyLoadCriteria(\JchOptimize\Core\Html\Parser $oParser, $bDeferred)
    {
        $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
        $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('script'));
        $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('noscript'));
        $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('textarea'));
        $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('template'));
        $oImgElement = new \JchOptimize\Core\Html\ElementObject();
        $oImgElement->bSelfClosing = \true;
        $oImgElement->setNamesArray(array('img'));
        //language=RegExp
        $oImgElement->addNegAttrCriteriaRegex('(?:data-(?:src|original))');
        $oImgElement->setCaptureAttributesArray(array('class', 'src', 'srcset', '(?:data-)?width', '(?:data-)?height'));
        $oParser->addElementObject($oImgElement);
        unset($oImgElement);
        $oInputElement = new \JchOptimize\Core\Html\ElementObject();
        $oInputElement->bSelfClosing = \true;
        $oInputElement->setNamesArray(array('input'));
        //language=RegExp
        $oInputElement->addPosAttrCriteriaRegex('type=(?>[\'"]?)image[\'"> ]');
        $oInputElement->setCaptureAttributesArray(array('class', 'src'));
        $oParser->addElementObject($oInputElement);
        unset($oInputElement);
        $oPictureElement = new \JchOptimize\Core\Html\ElementObject();
        $oPictureElement->setNamesArray(array('picture'));
        $oPictureElement->setCaptureAttributesArray(array('class'));
        $oPictureElement->bCaptureContent = \true;
        $oParser->addElementObject($oPictureElement);
        unset($oPictureElement);
        if (JCH_PRO) {
            /** @see LazyLoadExtended::setupLazyLoadExtended() */
            $this->container->get(LazyLoadExtended::class)->setupLazyLoadExtended($oParser, $bDeferred);
        }
    }
    public function processImageAttributes()
    {
        if ($this->params->get('img_attributes_enable', '0') || $this->params->get('lazyload_enable', '0') && $this->params->get('lazyload_autosize', '0')) {
            JCH_DEBUG ? Profiler::start('ProcessImageAttributes') : null;
            $oParser = new \JchOptimize\Core\Html\Parser();
            $oParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
            $oImgElement = new \JchOptimize\Core\Html\ElementObject();
            $oImgElement->setNamesArray(['img']);
            $oImgElement->bSelfClosing = \true;
            //language=RegExp
            $oImgElement->addPosAttrCriteriaRegex('width');
            //language=RegExp
            $oImgElement->addPosAttrCriteriaRegex('height');
            $oImgElement->bNegateCriteria = \true;
            $oImgElement->setCaptureAttributesArray(['data-src', 'src']);
            $oParser->addElementObject($oImgElement);
            try {
                $this->images = $oParser->findMatches($this->getBodyHtml());
            } catch (Exception\PregErrorException $oException) {
                $this->logger->error('Image Attributes matches failed: ' . $oException->getMessage());
            }
            JCH_DEBUG ? Profiler::stop('ProcessImageAttributes', \true) : null;
        }
    }
    public function processCdn()
    {
        if (!$this->params->get('cookielessdomain_enable', '0') || \trim($this->params->get('cookielessdomain', '')) == '' && \trim($this->params->get('pro_cookielessdomain_2', '')) == '' && \trim($this->params->get('pro_cookieless_3', '')) == '') {
            return \false;
        }
        JCH_DEBUG ? Profiler::start('RunCookieLessDomain') : null;
        $cdnCore = $this->container->get(CdnCore::class);
        $staticFiles = $cdnCore->getCdnFileTypes();
        $sf = \implode('|', $staticFiles);
        $oUri = new Uri(SystemUri::toString());
        $port = $oUri->toString(['port']);
        if (empty($port)) {
            $port = ':80';
        }
        $host = '(?:www\\.)?' . \preg_quote(\preg_replace('#^www\\.#i', '', $oUri->getHost()), '#') . '(?:' . $port . ')?';
        //Find base value in HTML
        $oBaseParser = new \JchOptimize\Core\Html\Parser();
        $oBaseElement = new \JchOptimize\Core\Html\ElementObject();
        $oBaseElement->setNamesArray(array('base'));
        $oBaseElement->bSelfClosing = \true;
        $oBaseElement->setCaptureAttributesArray(array('href'));
        $oBaseParser->addElementObject($oBaseElement);
        $aMatches = $oBaseParser->findMatches($this->getHeadHtml());
        unset($oBaseParser);
        unset($oBaseElement);
        $dir = \trim(SystemUri::basePath(), '/');
        //Adjust $dir if necessary based on <base/>
        if (!empty($aMatches[0])) {
            $oBaseUri = new Uri($aMatches[4][0]);
            //Remove filename from path
            $baseDir = \trim(\preg_replace('#/?[^/]*$#', '', $oBaseUri->getPath()), '/ \\n\\r\\t\\v\\0"');
            if ($baseDir != '') {
                $dir = $baseDir;
            }
        }
        //This part should match the scheme and host of a local file
        //language=RegExp
        $localhost = '(?:\\s*+(?:(?>https?:)?//' . $host . ')?)(?!http|//)';
        //language=RegExp
        $valueMatch = '(?!data:image)' . '(?=' . $localhost . ')' . '(?=((?<=")(?>\\.?[^.>"?]*+)*?\\.(?>' . $sf . ')(?=["?\\#])' . '|(?<=\')(?>\\.?[^.>\'?]*+)*?\\.(?>' . $sf . ')(?=[\'?\\#])' . '|(?<=\\()(?>\\.?[^.>)?]*+)*?\\.(?>' . $sf . ')(?=[)?\\#])' . '|(?<=^|[=\\s,])(?>\\.?[^.>\\s?]*+)*?\\.(?>' . $sf . ')(?=[\\s?\\#>]|$)))';
        try {
            //Get regex for <script> without src attribute
            $oElementParser = new \JchOptimize\Core\Html\Parser();
            $oElementWithCriteria = new \JchOptimize\Core\Html\ElementObject();
            $oElementWithCriteria->setNamesArray(array('script'));
            $oElementWithCriteria->addNegAttrCriteriaRegex('src');
            $oElementParser->addElementObject($oElementWithCriteria);
            $sScriptWithoutSrc = $oElementParser->getElementWithCriteria();
            unset($oElementParser);
            unset($oElementWithCriteria);
            //Process cdn for elements with href or src attributes
            $oSrcHrefParser = new \JchOptimize\Core\Html\Parser();
            $oSrcHrefParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
            $oSrcHrefParser->addExclude($sScriptWithoutSrc);
            $this->setUpCdnSrcHrefCriteria($oSrcHrefParser, $valueMatch);
            /** @var CdnCallback $cdnCallback */
            $cdnCallback = $this->container->get(CdnCallback::class);
            $cdnCallback->setDir($dir);
            $cdnCallback->setLocalhost($host);
            $sCdnHtml = $oSrcHrefParser->processMatchesWithCallback($this->getFullHtml(), $cdnCallback);
            unset($oSrcHrefParser);
            $this->setFullHtml($sCdnHtml);
            //Process cdn for CSS urls in style attributes or <style/> elements
            //language=RegExp
            $sUrlSearchRegex = '(?=((?>[^()<>]*+[()]?)*?[^()<>]*+(?<=url)\\((?>[\'"]?)' . $valueMatch . '))';
            $oUrlParser = new \JchOptimize\Core\Html\Parser();
            $oUrlParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
            $oUrlParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENTS(['script', 'link', 'meta']));
            $this->setUpCdnUrlCriteria($oUrlParser, $sUrlSearchRegex);
            $cdnCallback->setContext('cssurl');
            $cdnCallback->setSearchRegex($valueMatch);
            $sCdnUrlHtml = $oUrlParser->processMatchesWithCallback($this->getFullHtml(), $cdnCallback);
            unset($oUrlParser);
            $this->setFullHtml($sCdnUrlHtml);
            //Process cdn for elements with srcset attributes
            $oSrcsetParser = new \JchOptimize\Core\Html\Parser();
            $oSrcsetParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_COMMENT());
            $oSrcsetParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('script'));
            $oSrcsetParser->addExclude(\JchOptimize\Core\Html\Parser::HTML_ELEMENT('style'));
            $oSrcsetElement = new \JchOptimize\Core\Html\ElementObject();
            $oSrcsetElement->bSelfClosing = \true;
            $oSrcsetElement->setNamesArray(['img', 'source']);
            $oSrcsetElement->setCaptureOneOrBothAttributesArray(['srcset', 'data-srcset']);
            $oSrcsetElement->setValueCriteriaRegex('(?=.)');
            $oSrcsetParser->addElementObject($oSrcsetElement);
            $cdnCallback->setContext('srcset');
            $sCdnSrcsetHtml = $oSrcsetParser->processMatchesWithCallback($this->getBodyHtml(), $cdnCallback);
            unset($oSrcsetParser);
            unset($oSrcsetElement);
            $this->setBodyHtml($sCdnSrcsetHtml);
        } catch (Exception\PregErrorException $oException) {
            $this->logger->error('Cdn failed :' . $oException->getMessage());
        }
        JCH_DEBUG ? Profiler::stop('RunCookieLessDomain', \true) : null;
    }
    protected function setUpCdnSrcHrefCriteria(\JchOptimize\Core\Html\Parser $oParser, $sValueMatch)
    {
        $oSrcElement = new \JchOptimize\Core\Html\ElementObject();
        $oSrcElement->bSelfClosing = \true;
        $oSrcElement->setNamesArray(['img', 'script', 'source', 'input']);
        $oSrcElement->setCaptureOneOrBothAttributesArray(['src', 'data-src']);
        $oSrcElement->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oSrcElement);
        unset($oSrcElement);
        $oHrefElement = new \JchOptimize\Core\Html\ElementObject();
        $oHrefElement->bSelfClosing = \true;
        $oHrefElement->setNamesArray(['a', 'link', 'image']);
        $oHrefElement->setCaptureAttributesArray(['(?:xlink:)?href']);
        $oHrefElement->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oHrefElement);
        unset($oHrefElement);
        $oVideoElement = new \JchOptimize\Core\Html\ElementObject();
        $oVideoElement->bSelfClosing = \true;
        $oVideoElement->setNamesArray(['video']);
        $oVideoElement->setCaptureAttributesArray(['(?:src|poster)']);
        $oVideoElement->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oVideoElement);
        unset($oVideoElement);
        $oMediaElement = new \JchOptimize\Core\Html\ElementObject();
        $oMediaElement->bSelfClosing = \true;
        $oMediaElement->setNamesArray(['meta']);
        $oMediaElement->setCaptureAttributesArray(['content']);
        $oMediaElement->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oMediaElement);
        unset($oMediaElement);
    }
    public function getFullHtml()
    {
        return $this->html . $this->sRegexMarker;
    }
    public function setFullHtml($sHtml)
    {
        $this->html = $this->cleanRegexMarker($sHtml);
    }
    protected function setUpCdnUrlCriteria(\JchOptimize\Core\Html\Parser $oParser, $sValueMatch)
    {
        $oElements = new \JchOptimize\Core\Html\ElementObject();
        $oElements->bSelfClosing = \true;
        //language=RegExp
        $oElements->setNamesArray(array('(?!style|script|link|meta)[^\\s/"\'=<>]++'));
        $oElements->setCaptureAttributesArray(array('style'));
        $oElements->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oElements);
        unset($oElements);
        $oStyleElement = new \JchOptimize\Core\Html\ElementObject();
        $oStyleElement->setNamesArray(array('style'));
        $oStyleElement->bCaptureContent = \true;
        $oStyleElement->setValueCriteriaRegex($sValueMatch);
        $oParser->addElementObject($oStyleElement);
        unset($oStyleElement);
    }
    /**
     *
     * @return string
     */
    public function cleanHtml()
    {
        $aSearch = ['#' . \JchOptimize\Core\Html\Parser::HTML_HEAD_ELEMENT() . '#ix', '#' . \JchOptimize\Core\Html\Parser::HTML_COMMENT() . '#ix', '#' . \JchOptimize\Core\Html\Parser::HTML_ELEMENT('script') . '#ix', '#' . \JchOptimize\Core\Html\Parser::HTML_ELEMENT('style') . '#ix', '#' . \JchOptimize\Core\Html\Parser::HTML_ELEMENT('link', \true) . '#six'];
        $aReplace = ['<head><title></title></head>', '', '', '', ''];
        $html = \preg_replace($aSearch, $aReplace, $this->html);
        //Remove any hidden element from HtmL
        return \preg_replace_callback('#(<[^>]*+>)[^<>]*+#ix', function ($aMatches) {
            if (\preg_match('#type\\s*+=\\s*+["\']?hidden["\'\\s>]|\\shidden(?=[\\s>=])[^>\'"=]*+[>=]#i', $aMatches[1])) {
                return '';
            }
            //Add linebreak for readability during debugging
            return $aMatches[1] . "\n";
        }, $html);
    }
}
