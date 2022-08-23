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

use JchOptimize\Platform\Paths;
use Joomla\Uri\Uri;
class FileUtils
{
    /**
     * @var Cdn
     */
    private $cdn;
    public function __construct(\JchOptimize\Core\Cdn $cdn)
    {
        $this->cdn = $cdn;
    }
    /**
     * Get local path of file from the url in the HTML if internal.
     * If external or php file, the absolute url is returned
     *
     * @param   string  $url      Url of file
     * @param   string  $htmlUrl  Url of HTML in which the file is being processed
     *
     * @return string       File path
     */
    public function getPath(string $url, string $htmlUrl = '') : string
    {
        $fileUri = new Uri(\html_entity_decode($url));
        //If the url of the HTML is not provided we can use the base path of the system uri
        if ($htmlUrl == '') {
            $systemUri = new Uri(\JchOptimize\Core\SystemUri::toString());
            $basePath = \rtrim(\JchOptimize\Core\SystemUri::basePath(), '/');
        } else {
            //Use base path of home page
            $basePath = Paths::homeBasePath();
            $systemUri = new Uri($htmlUrl);
        }
        //Use absolute file path if file is internal and a static file
        if ($this->isInternal($url) && !\JchOptimize\Core\Url::requiresHttpProtocol($url)) {
            return Paths::absolutePath(\preg_replace('#^' . \preg_quote($basePath, '#') . '#', '', $fileUri->getPath()));
        } else {
            $scheme = $fileUri->getScheme();
            if (empty($scheme)) {
                $fileUri->setScheme($systemUri->getScheme());
            }
            $host = $fileUri->getHost();
            if (empty($host)) {
                $fileUri->setHost($systemUri->getHost());
            }
            $path = $fileUri->getPath();
            if (!empty($path)) {
                //If file is relative add the system base path
                if (\substr($path, 0, 1) != '/') {
                    $fileUri->setPath($basePath . '/' . $path);
                }
            }
            $filePath = $fileUri->toString();
            $query = $fileUri->getQuery();
            if (!empty($query)) {
                \parse_str($query, $args);
                $filePath = \str_replace($query, \http_build_query($args, '', '&'), $filePath);
            }
            return $filePath;
        }
    }
    /**
     * Determines if file is internal
     *
     * @param   string  $file  path of file
     *
     * @return boolean
     */
    public function isInternal(string $file) : bool
    {
        if (\JchOptimize\Core\Url::isProtocolRelative($file)) {
            $file = \JchOptimize\Core\Url::toAbsolute($file);
        }
        $uri = new Uri($file);
        $urlBase = $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path']);
        $urlHost = $uri->toString(['scheme', 'user', 'pass', 'host', 'port']);
        $domains = [\JchOptimize\Core\SystemUri::baseFull()];
        $domains = \array_merge($domains, \array_map(function ($cdnDomain) {
            return \JchOptimize\Core\Url::toAbsolute($cdnDomain);
        }, \array_keys($this->cdn->getCdnDomains())));
        foreach ($domains as $domain) {
            $domainUri = new Uri($domain);
            if (!(\stripos($urlBase, $domainUri->toString(['scheme', 'user', 'pass', 'host', 'port'])) !== 0 && !empty($urlHost))) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Prepare a representation of a file URL or value for display, possibly truncated
     *
     * @param   string  $value     The string being prepared
     * @param   bool    $url       Whether the string is the URL of a file
     * @param   bool    $truncate  If true will be truncated at specified length, prepending with an epsilon
     * @param   int     $length    The length in number of characters.
     *
     * @return string
     */
    public function prepareForDisplay(string $value, bool $url = \true, bool $truncate = \true, int $length = 27) : string
    {
        if ($url) {
            $oFile = new Uri($value);
            if ($this->isInternal($value)) {
                $value = $oFile->getPath();
            } else {
                $value = $oFile->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
            }
            if (!$truncate) {
                return $value;
            }
        }
        $eps = '';
        if (\strlen($value) > $length) {
            $value = \substr($value, -$length);
            $value = \preg_replace('#^[^/]*+/#', '/', $value);
            $eps = '...';
        }
        return $eps . $value;
    }
}
