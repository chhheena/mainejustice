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

use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\LinkBuilder;
use Joomla\DI\Container;
class Fonts extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    public static function appendOptimizedFontsToHtml(Container $container, $aFontFaceArray)
    {
        $aFonts = self::prepareFonts($aFontFaceArray);
        /** @var CacheManager $oCacheManager */
        $oCacheManager = $container->get(CacheManager::class);
        $oCacheManager->getCombinedFiles($aFonts, $fontsId, 'css');
        $linkBuilder = $container->get(LinkBuilder::class);
        $fontsUrl = self::optimizeFile($linkBuilder->buildUrl($fontsId, 'css'));
        $linkBuilder->appendOptimizedFontsToHead($fontsUrl);
    }
    private static function prepareFonts($aFontFaceArray) : array
    {
        $aFonts = [];
        foreach ($aFontFaceArray as $aFontFace) {
            $fontFaceCss = $aFontFace['content'];
            $aFonts[] = ['content' => $fontFaceCss, 'id' => \md5($fontFaceCss), 'match' => $fontFaceCss, 'media' => $aFontFace['media'], 'combining-fontface' => \true];
        }
        return $aFonts;
    }
    private static function optimizeFile($url) : string
    {
        return <<<HTML
<link rel="preload" as="style" href="{$url}" onload="this.rel='stylesheet'" >
HTML;
    }
}
