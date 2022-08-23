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

use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Browser;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Parser;
use JchOptimize\Platform\Paths;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
\defined('_JCH_EXEC') or die('Restricted access');
class Webp extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    public function convert($regex, $aMatches)
    {
        $elementName = !empty($aMatches[1]) ? $aMatches[1] : \false;
        $srcValue = !empty($aMatches[7]) ? $aMatches[7] : \false;
        $cssUrlValue = !empty($aMatches[9]) ? $aMatches[9] : \false;
        $srcsetValue = !empty($aMatches[10]) ? $aMatches[10] : \false;
        $newFullMatch = \false;
        if (!\in_array($elementName, ['img', 'input', 'picture', 'iframe', 'source', 'video', 'audio']) && $cssUrlValue) {
            $sWebpUrl = $this->getWebpImages($cssUrlValue);
            if ($sWebpUrl != $cssUrlValue) {
                $newFullMatch = $this->getNewFullMatch($cssUrlValue, $sWebpUrl, $aMatches);
            }
        } elseif (\in_array($elementName, ['img', 'input']) && $srcValue !== \false) {
            $sWebpUrl = $this->getWebpImages($srcValue);
            if ($sWebpUrl != $srcValue) {
                $newFullMatch = $this->getNewFullMatch($srcValue, $sWebpUrl, $aMatches);
            }
            if ($srcsetValue !== \false) {
                $aUrls = Helper::extractUrlsFromSrcset($srcsetValue);
                $aWebpUrls = \array_map(function ($v) {
                    return $this->getWebpImages($v);
                }, $aUrls);
                if ($aUrls != $aWebpUrls) {
                    $sSrcsetWebpValue = \str_replace($aUrls, $aWebpUrls, $srcsetValue);
                    $newFullMatch = $this->getNewFullMatch($aUrls, $aWebpUrls, $aMatches, $sSrcsetWebpValue);
                }
            }
        }
        if ($newFullMatch !== \false && \preg_match('#' . $regex . '#six', $newFullMatch, $aNewMatches)) {
            $aMatches = $aNewMatches;
        }
        return $aMatches;
    }
    public function getWebpImages($image) : string
    {
        if (\strpos($image, 'data:image') === 0 || !self::canIUse()) {
            return $image;
        }
        /** @var FileUtils $fileUtils */
        $fileUtils = $this->getContainer()->get(FileUtils::class);
        $imagePath = $fileUtils->getPath($image);
        //If path not absolute path on file system return
        if (\strpos($imagePath, Paths::rootPath()) === \false) {
            return $image;
        }
        $aPotentialPaths = [self::getWebpPathLegacy($imagePath), self::getWebpPath($imagePath)];
        foreach ($aPotentialPaths as $potentialWebpPath) {
            if (@\file_exists($potentialWebpPath)) {
                //replace file system path with root relative path
                $webpRootUrl = \str_replace(Paths::nextGenImagesPath(), Paths::nextGenImagesPath(\true), $potentialWebpPath);
                $oUri = new Uri($image);
                $oUri->setPath($webpRootUrl);
                return (\strpos($image, '//') === 0 ? '//' : '') . $oUri->toString();
            }
        }
        return $image;
    }
    /**
     * Tries to determine if client supports WEBP images based on https://caniuse.com/webp
     */
    protected static function canIUse() : bool
    {
        $oBrowser = Browser::getInstance();
        $browser = $oBrowser->getBrowser();
        //WEBP only supported in Safari running on MacOS 11 or higher, best to avoid.
        if ($browser == 'Internet Explorer' || $browser == 'Safari') {
            return \false;
        }
        return \true;
    }
    public static function getWebpPathLegacy($originalImagePath) : string
    {
        $file = \pathinfo(AdminHelper::contractFileNameLegacy($originalImagePath));
        return Paths::nextGenImagesPath() . '/' . $file['filename'] . '.webp';
    }
    public static function getWebpPath($originalImagePath) : string
    {
        $aFileParts = \pathinfo(AdminHelper::contractFileName($originalImagePath));
        return Paths::nextGenImagesPath() . '/' . $aFileParts['filename'] . '.webp';
    }
    /**
     * Rewrites the match for the Lazy-load feature
     *
     * @param   string|array  $urlValue         Value of original image url
     * @param   string|array  $webpUrl          Webp image url
     * @param   array         $aMatches         Match from the Lazy-load function
     * @param   string        $srcsetWebpValue  Srcset value containing webp urls
     *
     * @return array|string|string[]
     */
    protected function getNewFullMatch($urlValue, $webpUrl, array $aMatches, string $srcsetWebpValue = '')
    {
        //Don't process img if it contains srcset
        if ($this->params->get('pro_webp_old_browsers', '0') && $aMatches[1] == 'img') {
            $sSizesAttribute = '';
            $sWidthAttribute = @$aMatches[11] ?: '';
            if ($sWidthAttribute != '') {
                $sWidthAttribute = \strpos($sWidthAttribute, 'data-', 0) !== \false ? $sWidthAttribute : 'data-' . $sWidthAttribute;
            }
            $sHeightAttribute = @$aMatches[14] ?: '';
            if ($sHeightAttribute != '') {
                $sHeightAttribute = \strpos($sHeightAttribute, 'data-', 0) !== \false ? $sHeightAttribute : 'data-' . $sHeightAttribute;
            }
            if ($srcsetWebpValue !== '') {
                //If the image has a srcset value we want to put that on the <source/> element instead.
                $sNewSrcsetWebpValue = $srcsetWebpValue;
                //If there's a sizes attribute on the img element we want to use it
                if (\preg_match('#' . Parser::HTML_ATTRIBUTE_CP('sizes') . '#i', $aMatches[0], $aSizesMatches)) {
                    $sSizesAttribute = $aSizesMatches[0];
                }
            } else {
                $sNewSrcsetWebpValue = $webpUrl;
            }
            $sNewFullMatch = <<<HTML
<picture>
    <source srcset="{$sNewSrcsetWebpValue}" {$sWidthAttribute} {$sHeightAttribute} {$sSizesAttribute} type="image/webp">
    {$aMatches[0]}
</picture>
HTML;
        } else {
            $sNewFullMatch = \str_replace($urlValue, $webpUrl, $aMatches[0]);
        }
        return $sNewFullMatch;
    }
}
