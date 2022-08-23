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
use JchOptimize\Core\Cdn as CdnCore;
use JchOptimize\Core\Css\Parser as CssParser;
use Joomla\Registry\Registry;
class Cdn extends \JchOptimize\Core\Html\Callbacks\AbstractCallback
{
    /**
     * @var CdnCore
     */
    private $cdn;
    protected $context = 'default';
    protected $dir = '';
    protected $searchRegex = '';
    protected $localhost = '';
    public function __construct(Registry $params, CdnCore $cdn)
    {
        parent::__construct($params);
        $this->cdn = $cdn;
    }
    function processMatches($matches)
    {
        if (empty($matches[0])) {
            return $matches[0];
        }
        switch ($this->context) {
            case 'cssurl':
                //This would be either a <style> element, or an HTML element with a style attribute, containing one or more CSS urls
                $styleOrElement = $matches[0];
                $regex = 'url\\([\'"]?(' . $this->searchRegex . CssParser::CSS_URL_VALUE() . ')([\'"]?\\))';
                //Find all css urls in content
                \preg_match_all('#' . $regex . '#i', $styleOrElement, $aCssUrls, \PREG_SET_ORDER);
                //Prevent modifying the same url multiple times
                $aCssUrls = \array_unique($aCssUrls, \SORT_REGULAR);
                foreach ($aCssUrls as $aCssUrlMatch) {
                    $cssUrl = @$aCssUrlMatch[0] ?: \false;
                    $urlWithQuery = @$aCssUrlMatch[1] ?: \false;
                    $url = @$aCssUrlMatch[2];
                    if ($cssUrl !== \false && $url !== \false) {
                        $relRootUrl = $this->fixRelPath($url);
                        $cdnUrl = $this->cdn->loadCdnResource($relRootUrl, $url);
                        //First replace the url in the css url
                        $cdnCssUrl = \str_replace($urlWithQuery, $cdnUrl, $cssUrl);
                        //Replace the css url in content
                        $styleOrElement = \str_replace($cssUrl, $cdnCssUrl, $styleOrElement);
                    }
                }
                return $styleOrElement;
            case 'srcset':
                $fullMatch = @$matches[0] ?: \false;
                $srcSetAttr = @$matches[2] ?: \false;
                $srcSetValue = @$matches[4] ?: \false;
                $dataSrcSetAttr = (@$matches[5] ?: @$matches[8]) ?: \false;
                $dataSrcSetValue = (@$matches[7] ?: @$matches[10]) ?: \false;
                $returnMatch = $fullMatch;
                if ($srcSetAttr !== \false && $srcSetValue !== \false) {
                    $returnMatch = $this->handleSrcSetValues($srcSetAttr, $srcSetValue, $returnMatch);
                }
                if ($dataSrcSetAttr !== \false && $dataSrcSetValue !== \false) {
                    $returnMatch = $this->handleSrcSetValues($dataSrcSetAttr, $dataSrcSetValue, $returnMatch);
                }
                return $returnMatch;
            default:
                $fullMatch = @$matches[0] ?: \false;
                $hrefSrcAttr = @$matches[3] ?: \false;
                $hrefSrcValue = @$matches[5] ?: \false;
                $hrefSrcValueWithQuery = @$matches[6] ?: \false;
                $dataSrcAttr = (@$matches[7] ?: @$matches[11]) ?: \false;
                $dataSrcValue = (@$matches[9] ?: @$matches[13]) ?: \false;
                $dataSrcValueWithQuery = (@$matches[10] ?: @$matches[14]) ?: \false;
                $returnMatch = $fullMatch;
                if ($hrefSrcAttr !== \false && $hrefSrcValue !== \false) {
                    $rootRelSrcValue = $this->fixRelPath($hrefSrcValue);
                    $cdnSrcValue = $this->cdn->loadCdnResource($rootRelSrcValue, $hrefSrcValue);
                    //First replace the url in the src attribute
                    $cdnSrcAttr = \str_replace($hrefSrcValueWithQuery, $cdnSrcValue, $hrefSrcAttr);
                    //Then replace the original attribute with the attribute containing CDN url
                    $returnMatch = \str_replace($hrefSrcAttr, $cdnSrcAttr, $returnMatch);
                }
                if ($dataSrcAttr !== \false && $dataSrcValue !== \false) {
                    $rootRelDataSrcValue = $this->fixRelPath($dataSrcValue);
                    $cdnDataSrcValue = $this->cdn->loadCdnResource($rootRelDataSrcValue, $dataSrcValue);
                    //First replace the url in the data-src attribute
                    $cdnDataSrcAttr = \str_replace($dataSrcValueWithQuery, $cdnDataSrcValue, $dataSrcAttr);
                    //Then replace the original attribute with the attribute containing CDN url
                    $returnMatch = \str_replace($dataSrcAttr, $cdnDataSrcAttr, $returnMatch);
                }
                return $returnMatch;
        }
    }
    protected function handleSrcSetValues($attribute, $url, $returnMatch)
    {
        $cdnSrcSetAttr = $attribute;
        $regex = '(?:^|,)\\s*+(' . $this->searchRegex . '([^,]++))';
        \preg_match_all('#' . $regex . '#i', $url, $aUrls, \PREG_SET_ORDER);
        foreach ($aUrls as $aUrlMatch) {
            if (!empty($aUrlMatch[0])) {
                $url = $aUrlMatch[2];
                $rootRelUrl = $this->fixRelPath($url);
                $cdnUrl = $this->cdn->loadCdnResource($rootRelUrl, $url);
                $cdnSrcSetAttr = \str_replace($url, $cdnUrl, $cdnSrcSetAttr);
            }
        }
        $returnMatch = \str_replace($attribute, $cdnSrcSetAttr, $returnMatch);
        return $returnMatch;
    }
    protected function fixRelPath($path)
    {
        $sRegex = '^(?>https?:)?//' . $this->localhost;
        $path = \preg_replace('#' . $sRegex . '#i', '', \trim($path));
        if (\substr($path, 0, 1) != '/') {
            $path = '/' . $this->dir . '/' . $path;
        }
        return $path;
    }
    public function setDir($sDir)
    {
        $this->dir = $sDir;
    }
    public function setLocalhost($sLocalhost)
    {
        $this->localhost = $sLocalhost;
    }
    public function setContext($sContext)
    {
        $this->context = $sContext;
    }
    public function setSearchRegex($sSearchRegex)
    {
        $this->searchRegex = $sSearchRegex;
    }
}
