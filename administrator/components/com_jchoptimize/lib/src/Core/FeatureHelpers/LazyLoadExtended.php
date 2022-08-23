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
namespace JchOptimize\Core\FeatureHelpers;

use JchOptimize\Core\Css\Parser as CssParser;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\Parser;
\defined('_JCH_EXEC') or die('Restricted access');
class LazyLoadExtended extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    public static function lazyLoadAudioVideo($aMatches, $sReturn)
    {
        $sPosterAttribute = @$aMatches[5] ?: \false;
        return \str_replace($sPosterAttribute, 'data-' . $sPosterAttribute, $sReturn);
    }
    public static function negateAudioVideoPreload($aMatches, $sReturn)
    {
        $sElementName = @$aMatches[1] ?: \false;
        $sPreloadAttribute = @$aMatches[8] ?: \false;
        $sPreloadDelimiter = @$aMatches[9] ?: \false;
        $sAutoLoadAttribute = @$aMatches[11] ?: \false;
        if ($sPreloadAttribute !== \false) {
            $sNewPreloadAttribute = 'preload=' . $sPreloadDelimiter . 'none' . $sPreloadDelimiter;
            $sReturn = \str_replace($sPreloadAttribute, $sNewPreloadAttribute, $sReturn);
        } else {
            $sReturn = \str_replace('<' . $sElementName, '<' . $sElementName . ' preload="none"', $sReturn);
        }
        if ($sAutoLoadAttribute !== \false) {
            $sReturn = \str_replace($sAutoLoadAttribute, '', $sReturn);
        }
        return $sReturn;
    }
    public static function lazyLoadBgImages($aMatches, $sReturn)
    {
        $sStyleAttribute = @$aMatches[5] ?: \false;
        $sStyleDelimiter = @$aMatches[6] ?: \false;
        $sBgDeclaration = @$aMatches[7] ?: \false;
        $sCssUrl = @$aMatches[8] ?: \false;
        $sCssUrlValue = @$aMatches[9] ?: \false;
        $sNewStyleAttribute = \str_replace($sCssUrl, '', $sStyleAttribute);
        if (\strpos($sBgDeclaration, 'background-image') !== \false) {
            $sNewStyleAttribute = \str_replace($sBgDeclaration, 'background', $sNewStyleAttribute);
        }
        $sNewStyleAttribute = 'data-bg=' . $sStyleDelimiter . $sCssUrlValue . $sStyleDelimiter . ' ' . $sNewStyleAttribute;
        return \str_replace($sStyleAttribute, $sNewStyleAttribute, $sReturn);
    }
    public function setupLazyLoadExtended(Parser $oParser, $bDeferred)
    {
        if ($bDeferred && $this->params->get('pro_lazyload_iframe', '0')) {
            $oIframeElement = new ElementObject();
            $oIframeElement->setNamesArray(array('iframe'));
            $oIframeElement->setCaptureAttributesArray(array('class', 'src'));
            $oParser->addElementObject($oIframeElement);
            unset($oIframeElement);
        }
        if (!$bDeferred || $this->params->get('pro_lazyload_bgimages', '0') || $this->params->get('pro_next_gen_images', '1')) {
            $oBgElement = new ElementObject();
            $oBgElement->setNamesArray(array('[^\\s/"\'=<>]++'));
            $oBgElement->bSelfClosing = \true;
            $oBgElement->setCaptureAttributesArray(array('class', 'style'));
            //language=RegExp
            $sValueCriteriaRegex = '(?=(?>[^b>]*+b?)*?[^b>]*+(background(?:-image)?))' . '(?=(?>[^u>]*+u?)*?[^u>]*+(' . CssParser::CSS_URL_CP(\true) . '))';
            $oBgElement->setValueCriteriaRegex(array('style' => $sValueCriteriaRegex));
            $oParser->addElementObject($oBgElement);
            unset($oBgElement);
        }
        if ($bDeferred && $this->params->get('pro_lazyload_audiovideo', '0')) {
            $oVAElement = new ElementObject();
            $oVAElement->setNamesArray(array('video', 'audio'));
            $oVAElement->setCaptureAttributesArray(array('class', 'poster', 'preload', 'autoplay'));
            $oParser->addElementObject($oVAElement);
            unset($oVAElement);
        }
    }
    public static function getLazyLoadClass($aMatches)
    {
        return $aMatches[4];
    }
}
