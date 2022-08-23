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
namespace JchOptimize\Core;

use JchOptimize\Core\FeatureHelpers\Http2Excludes;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
// No direct access
\defined('_JCH_EXEC') or die('Restricted access');
class Http2Preload implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    private $bEnabled = \false;
    /**
     * @var Registry
     */
    private $params;
    /**
     * @var array
     */
    private $aPreloads = [];
    /**
     * @var Cdn
     */
    private $cdn;
    /**
     * @var FileUtils
     */
    private $fileUtils;
    private $includesAdded = \false;
    public function __construct(Registry $params, \JchOptimize\Core\Cdn $cdn, \JchOptimize\Core\FileUtils $fileUtils)
    {
        $this->params = $params;
        $this->cdn = $cdn;
        $this->fileUtils = $fileUtils;
        if ($params->get('http2_push_enable', '0')) {
            $this->bEnabled = \true;
        }
    }
    public function addIncludesToPreload()
    {
        if (JCH_PRO) {
            /** @see Http2Excludes::addHttp2Includes() */
            $this->container->get(Http2Excludes::class)->addHttp2Includes();
        }
    }
    /**
     * @param   string  $url  Url of file
     * @param   string  $type
     * @param   bool    $isDeferred
     *
     * @return false|void
     */
    public function add(string $url, string $type, bool $isDeferred = \false)
    {
        //Avoid invalid urls
        if ($url == '' || \JchOptimize\Core\Url::isDataUri(\trim($url))) {
            return \false;
        }
        if (JCH_PRO) {
            /** @see Http2Excludes::findHttp2Excludes() */
            if ($this->container->get(Http2Excludes::class)->findHttp2Excludes($url, $isDeferred)) {
                return \false;
            }
        }
        //Skip external files
        if (!$this->fileUtils->isInternal($url)) {
            return \false;
        }
        if ($this->params->get('cookielessdomain_enable', '0')) {
            static $sCdnFileTypesRegex = '';
            if (empty($sCdnFileTypesRegex)) {
                $sCdnFileTypesRegex = \implode('|', $this->cdn->getCdnFileTypes());
            }
            //If this file type will be loaded by CDN don't push if option not set
            if ($sCdnFileTypesRegex != '' && \preg_match('#\\.(?>' . $sCdnFileTypesRegex . ')#i', $url) && !$this->params->get('pro_http2_push_cdn', '0')) {
                return \false;
            }
        }
        if ($type == 'image') {
            static $no_image = 0;
            if ($no_image++ > 10) {
                return \false;
            }
        }
        if ($type == 'js') {
            static $no_js = 0;
            if ($no_js++ > 10) {
                return \false;
            }
            $type = 'script';
        }
        if ($type == 'css') {
            static $no_css = 0;
            if ($no_css++ > 10) {
                return \false;
            }
            $type = 'style';
        }
        if (!\in_array($type, $this->params->get('pro_http2_file_types', array('style', 'script', 'font', 'image')))) {
            return \false;
        }
        if ($type == 'font') {
            //Only push fonts of type woff/woff2
            if (\preg_match("#\\.\\K(?:woff2?|ttf)(?=\$|[\\#?])#", $url, $m) == '1') {
                static $no_font = 0;
                if ($no_font++ > 10) {
                    return \false;
                }
                $this->internalAdd($url, $type, $m[0]);
            } else {
                return \false;
            }
        } else {
            //Populate preload variable
            $this->internalAdd($url, $type);
        }
    }
    public function addAdditional(string $url, string $type, string $ext)
    {
        $this->internalAdd($url, $type, $ext);
    }
    /**
     * @param   string  $url
     * @param   string  $type
     * @param   string  $ext
     */
    private function internalAdd(string $url, string $type, string $ext = '') : void
    {
        $RR_url = \html_entity_decode($url);
        $preload = ['href' => $RR_url, 'as' => $type, 'crossorigin' => \false];
        if ($type == 'font') {
            $preload['crossorigin'] = \true;
            $ttfVersion = $preload;
            $woffVersion = $preload;
            $woff2Version = $preload;
            $ttfVersion['href'] = \preg_replace('#(?<=\\.)' . \preg_quote($ext) . '#', 'ttf', $preload['href']);
            $ttfVersion['type'] = 'font/ttf';
            $woffVersion['href'] = \preg_replace('#(?<=\\.)' . \preg_quote($ext) . '#', 'woff', $preload['href']);
            $woffVersion['type'] = 'font/woff';
            $woff2Version['href'] = \preg_replace('#(?<=\\.)' . \preg_quote($ext) . '#', 'woff2', $preload['href']);
            $woff2Version['type'] = 'font/woff2';
            switch ($ext) {
                case 'ttf':
                    //If we already have the woff or woff2 version, abort
                    if (\in_array($woffVersion, $this->aPreloads) || \in_array($woff2Version, $this->aPreloads)) {
                        return;
                    }
                    $preload = $ttfVersion;
                    break;
                case 'woff':
                    //If we already have the woff2 version of this file, abort
                    if (\in_array($woff2Version, $this->aPreloads)) {
                        return;
                    }
                    //if we already have the ttf version of this file, let's remove
                    //it and preload the woff version instead
                    $key = \array_search($ttfVersion, $this->aPreloads);
                    if ($key !== \false) {
                        unset($this->aPreloads[$key]);
                    }
                    $preload = $woffVersion;
                    break;
                case 'woff2':
                    //If we already have the woff version of this file,
                    // let's remove it and preload the woff2 version instead
                    $woff_key = \array_search($woffVersion, $this->aPreloads);
                    if ($woff_key !== \false) {
                        unset($this->aPreloads[$woff_key]);
                    }
                    //If we already have the ttf version of this file,
                    //let's remove it also
                    $ttf_key = \array_search($ttfVersion, $this->aPreloads);
                    if ($ttf_key !== \false) {
                        unset($this->aPreloads[$ttf_key]);
                    }
                    $preload = $woff2Version;
                    break;
                default:
                    break;
            }
        }
        if (!\in_array($preload, $this->aPreloads)) {
            $this->aPreloads[] = $preload;
        }
    }
    public function getPreloads() : array
    {
        if (!$this->includesAdded) {
            $this->addIncludesToPreload();
            $this->includesAdded = \true;
        }
        $preloads = $this->aPreloads;
        $this->aPreloads = [];
        return $preloads;
    }
    public function isEnabled() : bool
    {
        return $this->bEnabled;
    }
}
