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
namespace JchOptimize\Core\Css\Callbacks;

\defined('_JCH_EXEC') or die('Restricted access');
use JchOptimize\Core\Combiner;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FeatureHelpers\GoogleFonts;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Url;
class HandleAtRules extends \JchOptimize\Core\Css\Callbacks\AbstractCallback
{
    private $atImports = [];
    private $fontFace = '';
    /**
     * @var
     */
    private $cssInfos;
    public function processMatches($matches, $context)
    {
        if ($context == 'charset') {
            return '';
        }
        if ($context == 'font-face') {
            if (!\preg_match('#font-display#i', $matches[0])) {
                $matches[0] = \preg_replace('#;?\\s*}$#', ';font-display:swap;}', $matches[0]);
            }
            if ($this->params->get('pro_optimize_fonts', '0') && empty($this->cssInfos['combining-fontface'])) {
                $this->fontFace .= $matches[0];
                return '';
            }
            return $matches[0];
        }
        //At this point we should be in import context
        $url = $matches[3];
        $media = $matches[4];
        if ($this->params->get('pro_optimize_gfont_enable', '0') && \strpos($url, 'fonts.googleapis.com') !== \false) {
            /** @see GoogleFonts::optimizeFile() */
            $this->getContainer()->get(GoogleFonts::class)->optimizeFile($url, $media);
            return '';
        }
        if (!$this->params->get('replaceImports', '0')) {
            $this->atImports[] = $matches[0];
            return '';
        }
        /** @var FilesManager $oFilesManager */
        $oFilesManager = $this->getContainer()->get(FilesManager::class);
        if (empty($url) || !$oFilesManager->isHttpAdapterAvailable($url) || Url::isSSL($url) && !\extension_loaded('openssl') || !Url::isHttpScheme($url)) {
            return $matches[0];
        }
        if ($oFilesManager->isDuplicated($url)) {
            return '';
        }
        $aUrlArray = array();
        $aUrlArray[0]['url'] = $url;
        $aUrlArray[0]['media'] = $media;
        /** @var Combiner $oCombiner */
        $oCombiner = $this->getContainer()->get(Combiner::class);
        try {
            $sFileContents = $oCombiner->combineFiles($aUrlArray, 'css');
        } catch (Exception\ExceptionInterface $e) {
            return $matches[0];
        }
        return $sFileContents['content'];
    }
    public function setCssInfos($cssInfos)
    {
        $this->cssInfos = $cssInfos;
    }
    public function getImports() : array
    {
        return $this->atImports;
    }
    public function getFontFace() : string
    {
        return $this->fontFace;
    }
}
