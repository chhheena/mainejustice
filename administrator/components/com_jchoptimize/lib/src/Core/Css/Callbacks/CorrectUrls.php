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
use JchOptimize\Core\Cdn;
use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\FeatureHelpers\Webp;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Url;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
class CorrectUrls extends \JchOptimize\Core\Css\Callbacks\AbstractCallback
{
    public $isHttp2 = \false;
    public $isFontsOnly = \false;
    public $isBackend = \false;
    /**
     * @var Cdn
     */
    public $cdn;
    /**
     * @var Http2Preload
     */
    public $http2Preload;
    private $images = [];
    /**
     * @var array
     */
    private $cssInfos;
    /**
     * @var FileUtils
     */
    private $fileUtils;
    public function __construct(Registry $params, Cdn $cdn, Http2Preload $http2Preload, FileUtils $fileUtils)
    {
        parent::__construct($params);
        $this->cdn = $cdn;
        $this->http2Preload = $http2Preload;
        $this->fileUtils = $fileUtils;
    }
    public function processMatches($matches, $context)
    {
        $sRegex = '(?>u?[^u]*+)*?\\K(?:' . Parser::CSS_URL_CP(\true) . '|$)';
        if ($context == 'import') {
            $sRegex = Parser::CSS_AT_IMPORT_CP(\true);
        }
        return \preg_replace_callback('#' . $sRegex . '#i', function ($aInnerMatches) use($context) {
            return $this->processInnerMatches($aInnerMatches, $context);
        }, $matches[0]);
    }
    protected function processInnerMatches($matches, $context)
    {
        if (empty($matches[0])) {
            return $matches[0];
        }
        $sOriginalImageUrl = \trim($matches[1]);
        if (Url::isHttpScheme($sOriginalImageUrl)) {
            if ($this->isHttp2 && $this->fileUtils->isInternal($sOriginalImageUrl)) {
                $sFileType = $context == 'font-face' ? 'font' : 'image';
                if ($this->isFontsOnly && $sFileType != 'font') {
                    return \false;
                }
                $this->http2Preload->add($sOriginalImageUrl, $sFileType);
                return \true;
            }
            $sCssFileUrl = empty($this->cssInfos['url']) ? '' : $this->cssInfos['url'];
            if (($sCssFileUrl == '' || $this->fileUtils->isInternal($sCssFileUrl)) && $this->fileUtils->isInternal($sOriginalImageUrl)) {
                $imageUrl = Url::toRootRelative($sOriginalImageUrl, $sCssFileUrl);
                $oImageUri = new Uri($imageUrl);
                //Have to set path to have it cleaned, ie /foo/bar/../boo.php	=> /foo/boo.php
                $oImageUri->setPath($oImageUri->getPath());
                $imageUrl = $oImageUri->toString();
                $imageUrlCdn = $this->cdn->loadCdnResource($oImageUri->getPath(), $imageUrl);
                //Handle font files while we're enabling CDN feature
                if ($this->params->get('cookielessdomain_enable', '0') && $context == 'font-face') {
                    $oUri = new Uri(SystemUri::toString());
                    //Are we loading fonts over cdn?
                    $isFontsLoadingOnCdn = $this->params->get('pro_cdn_load_fonts', '0');
                    //Did we get a CDN file for this font?
                    $isCdnAvailableForFont = !($imageUrlCdn == $imageUrl);
                    //If we're using fonts on CDN, and we have one go ahead
                    if ($isFontsLoadingOnCdn && $isCdnAvailableForFont) {
                        $imageUrl = $imageUrlCdn;
                    } else {
                        $imageUrl = '//' . $oUri->toString(['host', 'port']) . $oImageUri->toString(['path', 'query', 'fragment']);
                    }
                } else {
                    //If CSS file will be loaded by CDN but image won't, then return absolute url
                    if ($this->params->get('cookielessdomain_enable', '0') && \in_array('css', $this->cdn->getCdnFileTypes()) && $imageUrlCdn == $imageUrl) {
                        $imageUrl = Url::toAbsolute($imageUrl);
                    } else {
                        $imageUrl = $imageUrlCdn;
                    }
                }
            } else {
                if (!Url::isAbsolute($sOriginalImageUrl)) {
                    $imageUrl = Url::toAbsolute($sOriginalImageUrl, $sCssFileUrl);
                } else {
                    return $matches[0];
                }
            }
            if ($this->isBackend && $context != 'font-face') {
                $this->images[] = $imageUrl;
            }
            if (JCH_PRO && $this->params->get('pro_next_gen_images')) {
                /** @see Webp::getWebpImages() */
                $imageUrl = $this->getContainer()->get(Webp::class)->getWebpImages($imageUrl);
            }
            // If URL without quotes and contains any parentheses, whitespace characters,
            // single quotes (') and double quotes (") that are part of the URL, quote URL
            if (\strpos($matches[0], 'url(' . $sOriginalImageUrl . ')') !== \false && \preg_match('#[()\\s\'"]#', $imageUrl)) {
                $imageUrl = '"' . $imageUrl . '"';
            }
            return \str_replace($sOriginalImageUrl, $imageUrl, $matches[0]);
        } else {
            return $matches[0];
        }
    }
    public function setCssInfos($cssInfos)
    {
        $this->cssInfos = $cssInfos;
    }
    public function getImages()
    {
        return $this->images;
    }
}
