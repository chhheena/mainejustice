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
use JchOptimize\Core\FeatureHelpers\LazyLoadExtended;
use JchOptimize\Core\FeatureHelpers\Webp;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\Parser;
use JchOptimize\Core\Http2Preload;
use Joomla\Registry\Registry;
class LazyLoad extends \JchOptimize\Core\Html\Callbacks\AbstractCallback
{
    /**
     * @var bool $isExcluded Used to indicate when the child of a parent element is excluded so the whole element
     *           can be excluded
     */
    public $isExcluded = \false;
    /**
     * @var Http2Preload
     */
    public $http2Preload;
    /**
     * @var array
     */
    protected $excludes;
    /**
     * @var array
     */
    protected $args;
    public function __construct(Registry $params, Http2Preload $http2Preload)
    {
        parent::__construct($params);
        $this->http2Preload = $http2Preload;
        $this->getLazyLoadExcludes();
    }
    protected function getLazyLoadExcludes()
    {
        $aExcludesFiles = Helper::getArray($this->params->get('excludeLazyLoad', array()));
        $aExcludesFolders = Helper::getArray($this->params->get('pro_excludeLazyLoadFolders', array()));
        $aExcludesUrl = \array_merge(array('data:image'), $aExcludesFiles, $aExcludesFolders);
        $aExcludeClass = Helper::getArray($this->params->get('pro_excludeLazyLoadClass', array()));
        $this->excludes = array('url' => $aExcludesUrl, 'class' => $aExcludeClass);
    }
    function processMatches($matches)
    {
        if (empty($matches[0])) {
            return $matches[0];
        }
        if (JCH_PRO && $this->params->get('pro_next_gen_images', '1') && $this->args['parent'] != 'picture') {
            /** @see Webp::convert() */
            $matches = $this->getContainer()->get(Webp::class)->convert($this->regex, $matches);
        }
        $sFullMatch = !empty($matches[0]) ? $matches[0] : \false;
        $sElementName = !empty($matches[1]) ? $matches[1] : \false;
        $sClassAttribute = !empty($matches[2]) ? $matches[2] : \false;
        $sClassDelimiter = !empty($matches[3]) ? $matches[3] : \false;
        $sClassValue = !empty($matches[4]) ? $matches[4] : \false;
        $sSrcAttribute = $sPosterAttribute = $sInnerContent = $sStyleAttribute = !empty($matches[5]) ? $matches[5] : \false;
        $sSrcDelimiter = $sPosterDelimiter = $sStyleDelimiter = !empty($matches[6]) ? $matches[6] : \false;
        $sSrcValue = $sPosterValue = $sBgDeclaration = !empty($matches[7]) ? $matches[7] : \false;
        $sSrcsetAttribute = $sPreloadAttribute = $sCssUrl = !empty($matches[8]) ? $matches[8] : \false;
        $sSrcsetDelimiter = $sPreloadDelimiter = $sCssUrlValue = !empty($matches[9]) ? $matches[9] : \false;
        $sSrcsetValue = $sPreloadValue = !empty($matches[10]) ? $matches[10] : \false;
        $sAutoLoadAttribute = $sWidthAttribute = !empty($matches[11]) ? $matches[11] : \false;
        $sWidthDelimiter = !empty($matches[12]) ? $matches[12] : \false;
        $sWidthValue = !empty($matches[13]) ? $matches[13] : 1;
        $sHeightAttribute = !empty($matches[14]) ? $matches[14] : \false;
        $sHeightDelimiter = !empty($matches[15]) ? $matches[15] : \false;
        $sHeightValue = !empty($matches[16]) ? $matches[16] : 1;
        $isLazyLoaded = \false;
        //Return match if it isn't an HTML element
        if ($sElementName === \false) {
            return $sFullMatch;
        }
        switch ($sElementName) {
            case 'img':
            case 'input':
            case 'picture':
            case 'iframe':
            case 'source':
                $sImgType = 'embed';
                break;
            case 'video':
            case 'audio':
                $sImgType = 'audiovideo';
                break;
            default:
                $sImgType = 'background';
                break;
        }
        if ($this->args['lazyload']) {
            if ($sElementName == 'img' || $sElementName == 'input') {
                $this->http2Preload->add($sSrcValue, 'image', \true);
            }
            //Start modifying the element to return
            $return = $sFullMatch;
            if ($sElementName != 'picture') {
                //If a src attribute is found
                if ($sSrcAttribute !== \false) {
                    $sImgName = $sImgType == 'embed' ? $sSrcValue : $sCssUrlValue;
                    //Abort if this file is excluded
                    if (Helper::findExcludes($this->excludes['url'], $sImgName) || $sElementName && Helper::findExcludes($this->excludes['class'], $sClassValue)) {
                        //If element child of a parent element set excluded flag
                        if ($this->args['parent'] != '') {
                            $this->isExcluded = \true;
                        }
                        return $sFullMatch;
                    }
                    //If no srcset attribute was found, modify the src attribute and add a data-src attribute
                    if ($sSrcsetAttribute === \false && $sImgType == 'embed') {
                        $sSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $sWidthValue . '" height="' . $sHeightValue . '"></svg>';
                        $sNewSrcValue = $sElementName == 'iframe' ? 'about:blank' : 'data:image/svg+xml;base64,' . \base64_encode($sSvg);
                        $sNewSrcAttribute = 'src=' . $sSrcDelimiter . $sNewSrcValue . $sSrcDelimiter . ' data-' . $sSrcAttribute;
                        $return = \str_replace($sSrcAttribute, $sNewSrcAttribute, $return);
                        $isLazyLoaded = \true;
                    }
                    if (JCH_PRO && $sImgType == 'audiovideo') {
                        /** @see LazyLoadExtended::lazyLoadAudioVideo() */
                        $return = $this->getContainer()->get(LazyLoadExtended::class)->lazyLoadAudioVideo($matches, $return);
                        $isLazyLoaded = \true;
                    }
                }
                //Modern browsers will lazy-load without loading the src attribute
                if ($sSrcsetAttribute !== \false && $sImgType == 'embed') {
                    $sSvgSrcset = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $sWidthValue . '" height="' . $sHeightValue . '"></svg>';
                    $sNewSrcsetAttribute = 'srcset=' . $sSrcsetDelimiter . 'data:image/svg+xml;base64,' . \base64_encode($sSvgSrcset) . $sSrcsetDelimiter . ' data-' . $sSrcsetAttribute;
                    $return = \str_replace($sSrcsetAttribute, $sNewSrcsetAttribute, $return);
                    $isLazyLoaded = \true;
                }
                if (JCH_PRO && $sImgType == 'audiovideo') {
                    /** @see LazyLoadExtended::negateAudioVideoPreload() */
                    $return = $this->getContainer()->get(LazyLoadExtended::class)->negateAudioVideoPreload($matches, $return);
                    $isLazyLoaded = \true;
                }
            }
            //Process and add content of element if not self-closing
            if ($sElementName == 'picture' && $sInnerContent !== \false) {
                $sInnerContentLazyLoaded = $this->lazyLoadInnerContent($sInnerContent);
                //If any child element were lazyloaded this function will return false
                if ($sInnerContentLazyLoaded === \false) {
                    return $sFullMatch;
                }
                return \str_replace($sInnerContent, $sInnerContentLazyLoaded, $sFullMatch);
            }
            if (JCH_PRO && $sImgType == 'background' && $this->params->get('pro_lazyload_bgimages', '0')) {
                /** @see LazyLoadExtended::lazyLoadBgImages() */
                $return = $this->getContainer()->get(LazyLoadExtended::class)->lazyLoadBgImages($matches, $return);
                $isLazyLoaded = \true;
            }
            if ($isLazyLoaded) {
                //If class attribute not on the appropriate element add it
                if ($sElementName != 'source' && $sClassAttribute === \false) {
                    $return = \str_replace('<' . $sElementName, '<' . $sElementName . ' class="jch-lazyload"', $return);
                }
                //If class already on element add the lazy-load class
                if ($sElementName != 'source' && $sClassAttribute !== \false) {
                    $sNewClassAttribute = 'class=' . $sClassDelimiter . $sClassValue . ' jch-lazyload' . $sClassDelimiter;
                    $return = \str_replace($sClassAttribute, $sNewClassAttribute, $return);
                }
            }
            if ($this->args['parent'] != 'picture' && $isLazyLoaded) {
                //Wrap and add img elements in noscript
                if ($sElementName == 'img' || $sElementName == 'iframe') {
                    $return .= '<noscript>' . $sFullMatch . '</noscript>';
                }
            }
            return $return;
        } else {
            if ($sSrcAttribute !== \false && ($sElementName == 'img' || $sElementName == 'input')) {
                $this->http2Preload->add($sSrcValue, 'image', $this->args['deferred']);
            }
            if ($sImgType == 'background' && $sStyleAttribute !== \false) {
                $this->http2Preload->add($sCssUrlValue, 'image', $this->args['deferred']);
            }
            return $sFullMatch;
        }
    }
    protected function lazyLoadInnerContent($innerContent)
    {
        $oParser = new Parser();
        $oImgElement = new ElementObject();
        $oImgElement->bSelfClosing = \true;
        $oImgElement->setNamesArray(array('img', 'source'));
        //language=RegExp
        $oImgElement->addNegAttrCriteriaRegex('(?:data-(?:src|original))');
        $oImgElement->setCaptureAttributesArray(array('class', 'src', 'srcset', '(?:data-)?width', '(?:data-)?height'));
        $oParser->addElementObject($oImgElement);
        $args = ['lazyload' => \true, 'deferred' => \true, 'parent' => 'picture'];
        /** @var LazyLoad $lazyLoadCallback */
        $lazyLoadCallback = $this->getContainer()->get(\JchOptimize\Core\Html\Callbacks\LazyLoad::class);
        $lazyLoadCallback->setLazyLoadArgs($args);
        $result = $oParser->processMatchesWithCallback($innerContent, $lazyLoadCallback);
        //if any child element were excluded return false
        if ($lazyLoadCallback->isExcluded) {
            return \false;
        }
        return $result;
    }
    public function setLazyLoadArgs($args)
    {
        $this->args = $args;
    }
}
