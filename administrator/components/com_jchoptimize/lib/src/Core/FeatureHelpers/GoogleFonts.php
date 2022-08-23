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
namespace JchOptimize\Core\FeatureHelpers;

use JchOptimize\Core\Exception;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\Parser;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use Psr\Log\LoggerInterface;
\defined('_JCH_EXEC') or die('Restricted access');
class GoogleFonts extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    public $isGoogleFontsOptimized = \false;
    public $googleFonts = [];
    public function getPreconnect() : string
    {
        return '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />';
    }
    /**
     * Optimizes an array of Google Font files. Files imported in CSS files will be returned as an array when the files are combined
     *
     * @param                $aGFonts
     *
     * @return void
     */
    public function optimizeFiles($aGFonts) : void
    {
        foreach ($aGFonts as $aGFontArray) {
            $sUrl = $aGFontArray['url'];
            $sMedia = $aGFontArray['media'];
            $this->optimizeFile($sUrl, $sMedia);
        }
    }
    /**
     * Optimizes a single Google Font file. Used in FileManager when the CombineJSCSS is being processed
     *
     * @param $url
     * @param $media
     *
     * @return void
     */
    public function optimizeFile($url, $media) : void
    {
        if (\strpos($url, 'display=swap') === \false) {
            $url .= '&display=swap';
        }
        if ($media == 'none') {
            $media = 'all';
        }
        $mediaAttr = $media != '' ? ' media="' . $media . '" ' : '';
        $this->isGoogleFontsOptimized = \true;
        //language=HTML
        $this->googleFonts[] = '<link rel="preload" as="style" href="' . $url . '" ' . $mediaAttr . ' onload="this.rel=\'stylesheet\'" />';
    }
    public function isGFontPreConnected()
    {
        if ($this->params->get('pro_optimize_gfonts_enabled', '0')) {
            try {
                $oGFParser = new Parser();
                $oGFParser->addExclude(Parser::HTML_COMMENT());
                $oGFElement = new ElementObject();
                $oGFElement->setNamesArray(array('link'));
                $oGFElement->addPosAttrCriteriaRegex('rel==[\'"]?preconnect[\'"> ]');
                $oGFElement->addPosAttrCriteriaRegex('href==[\'"]?https?://fonts.gstatic.com[\'"> ]');
                $oGFElement->bSelfClosing = \true;
                $oGFParser->addElementObject($oGFElement);
                /** @var HtmlProcessor $oProcessor */
                $oProcessor = $this->getContainer()->get(HtmlProcessor::class);
                $aMatches = $oGFParser->findMatches($oProcessor->getHeadHtml());
                if (!empty($aMatches[0])) {
                    return \true;
                }
            } catch (Exception\ExceptionInterface $oException) {
                $logger = $this->getContainer()->get(LoggerInterface::class);
                $logger->error('Failed searching for Gfont preconnect: ' . $oException->getMessage());
            }
            return \false;
        }
    }
}
