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

use Joomla\Uri\Uri;
\defined('_JCH_EXEC') or die('Restricted access');
abstract class Url
{
    /**
     * Check if url is protocol relative
     *
     * @param   string  $url
     *
     * @return boolean
     */
    public static function isProtocolRelative(string $url) : bool
    {
        return \preg_match('#^//#', $url);
    }
    /**
     * Returns the absolute url of a relative url in a file
     *
     * @param   string  $url           Url to modify
     * @param   string  $externalFile  Current file that contains the url or use uri of server if url is in an inline declaration.
     *
     * @return string
     */
    public static function toAbsolute(string $url, string $externalFile = 'SERVER') : string
    {
        //If file path already absolute just return
        if (self::isAbsolute($url)) {
            return $url;
        }
        $oExternalURI = new Uri($externalFile);
        $oCurrentURI = new Uri($url);
        $sCurrentHost = $oCurrentURI->getHost();
        //If url is relative add to external uri path
        if (self::isPathRelative($url)) {
            $oCurrentURI->setPath(\dirname($oExternalURI->getPath()) . '/' . \preg_replace('#[?\\#].*+#', '', $url));
        }
        //Update current url with scheme and host of external file
        $sExternalHost = $oExternalURI->getHost();
        $sExternalScheme = $oExternalURI->getScheme();
        //Only add host if current file is without host
        if (!empty($sExternalHost) && empty($sCurrentHost)) {
            $oCurrentURI->setHost($sExternalHost);
        }
        if (!empty($sExternalScheme)) {
            $oCurrentURI->setScheme($sExternalScheme);
        }
        $sAbsUrl = $oCurrentURI->toString();
        $host = $oCurrentURI->getHost();
        //If url still not absolute but contains a host then return a protocol relative url
        if (!self::isAbsolute($sAbsUrl) && !empty($host)) {
            return '//' . $sAbsUrl;
        }
        return $sAbsUrl;
    }
    /**
     * Check is url is an absolute path
     *
     * @param   string  $url
     *
     * @return boolean
     */
    public static function isAbsolute(string $url) : bool
    {
        return \preg_match('#^http#i', $url);
    }
    /**
     * Checks if url is a relative path
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function isPathRelative(string $url) : bool
    {
        return self::isHttpScheme($url) && !self::isAbsolute($url) && !self::isProtocolRelative($url) && !self::isRootRelative($url);
    }
    /**
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function isHttpScheme(string $url) : bool
    {
        return !\preg_match('#^(?!https?)[^:/]+:#i', $url);
    }
    /**
     * Checks if url is relative to the document root
     *
     * @param   string  $url
     *
     * @return boolean
     */
    public static function isRootRelative(string $url) : bool
    {
        return \preg_match('#^/[^/]#', $url);
    }
    /**
     * Checks if url is using ssl
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function isSSL(string $url) : bool
    {
        return \preg_match('#^https#i', $url);
    }
    /**
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function isDataUri(string $url) : bool
    {
        return \preg_match('#^data:#i', $url);
    }
    /**
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function isInvalid(string $url) : bool
    {
        return empty($url) || \trim($url) == '/' || \trim($url, ' /\\') == \trim(\JchOptimize\Core\SystemUri::baseFull(), ' /\\') || \trim($url, ' /\\') == \trim(\JchOptimize\Core\SystemUri::basePath(), ' /\\');
    }
    /**
     * Changes an absolute url to a protocol relative url
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function AbsToProtocolRelative(string $url) : bool
    {
        return \preg_replace('#https?:#i', '', $url);
    }
    /**
     * Changes a url to a root relative url
     *
     * @param   string  $url
     * @param   string  $currentFile  File path that the url is found in.
     *
     * @return string
     */
    public static function toRootRelative(string $url, string $currentFile = '') : string
    {
        if (self::isPathRelative($url)) {
            $url = (empty($currentFile) ? '' : \dirname($currentFile) . '/') . $url;
        }
        $url = (new Uri($url))->toString(array('path', 'query', 'fragment'));
        if (self::isPathRelative($url)) {
            $url = \rtrim(\JchOptimize\Core\SystemUri::basePath(), '\\/') . '/' . $url;
        }
        return $url;
    }
    /**
     * Determines if this url will need to be accessed using an http adapter
     *
     * @param   string  $url
     *
     * @return bool
     */
    public static function requiresHttpProtocol(string $url) : bool
    {
        return \preg_match('#\\.php|^(?![^?\\#]*\\.(?:css|js|png|jpe?g|gif|bmp|webp)(?:[?\\#]|$)).++#i', $url);
    }
}
