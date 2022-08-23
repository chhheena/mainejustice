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

use http\QueryString;
use JchOptimize\Platform\Paths;
use Joomla\Input\Input;
use Joomla\Uri\Uri;
use function dirname;
/**
 * Class to provide info about the current request URI from the server
 */
class SystemUri
{
    /**
     * private instance of class
     *
     * @var SystemUri
     */
    private static $instance;
    /**
     * Input object used internally
     *
     * @var Input
     */
    private $input;
    /**
     * The detected current url
     *
     * @var string
     */
    private $requestUrl;
    /**
     * Path to index.php including host relative to the home page
     *
     * @var string
     */
    private $baseFull;
    /**
     * Path to index.php excluding the host relative to the home page
     *
     * @var string
     */
    private $basePath;
    /**
     * Path to index.php including host relative to the current request
     *
     * @var string
     */
    private $currentBaseFull;
    /**
     * Path to the index.php excluding host relative to the current request
     *
     * @var string
     */
    private $currentBasePath;
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->input = new Input();
        $this->requestUrl = $this->detectRequestUri();
        $uri = new Uri($this->requestUrl);
        $requestUri = $this->input->server->getString('REQUEST_URI', '');
        // If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
        if (\strpos(\PHP_SAPI, 'cgi') !== \false && !\ini_get('cgi.fix_pathinfo') && !empty($requestUri)) {
            // We aren't expecting PATH_INFO within PHP_SELF so this should work.
            $path = dirname($this->input->server->getString('PHP_SELF', ''));
        } else {
            // Pretty much everything else should be handled with SCRIPT_NAME.
            $path = dirname($this->input->server->getString('SCRIPT_NAME', ''));
        }
        //get the host from the URI
        $host = $uri->toString(['scheme', 'user', 'pass', 'host', 'port']);
        // Check if the path includes "index.php".
        if (\strpos($path, 'index.php') !== \false) {
            // Remove the index.php portion of the path.
            $path = \substr_replace($path, '', \strpos($path, 'index.php'), 9);
        }
        $path = \rtrim($path, '/\\');
        $this->currentBaseFull = $host . $path . '/';
        $this->currentBasePath = $path . '/';
        $this->baseFull = \rtrim(Paths::homeBaseFullPath(), '/') . '/';
        $this->basePath = \rtrim(Paths::homeBasePath(), '/') . '/';
    }
    /**
     * Method to detect the requested URI from server environment variables.
     *
     * @return  string  The requested URI
     */
    private function detectRequestUri() : string
    {
        // First we need to detect the URI scheme.
        $scheme = $this->isSslConnection() ? 'https://' : 'http://';
        /*
         * There are some differences in the way that Apache and IIS populate server environment variables.  To
         * properly detect the requested URI we need to adjust our algorithm based on whether we are getting
         * information from Apache or IIS.
         */
        $phpSelf = $this->input->server->getString('PHP_SELF', '');
        $requestUri = $this->input->server->getString('REQUEST_URI', '');
        // If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
        if (!empty($phpSelf) && !empty($requestUri)) {
            // The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
            $uri = $scheme . $this->input->server->getString('HTTP_HOST') . $requestUri;
        } else {
            // If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
            // IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
            $uri = $scheme . $this->input->server->getString('HTTP_HOST') . $this->input->server->getString('SCRIPT_NAME');
            $queryHost = $this->input->server->getString('QUERY_STRING', '');
            // If the QUERY_STRING variable exists append it to the URI string.
            if (!empty($queryHost)) {
                $uri .= '?' . $queryHost;
            }
        }
        return \trim($uri);
    }
    /**
     * Determine if we are using a secure (SSL) connection.
     *
     * @return  boolean  True if using SSL, false if not.
     *
     */
    private function isSslConnection() : bool
    {
        $serverSSLVar = $this->input->server->getString('HTTPS', '');
        if (!empty($serverSSLVar) && \strtolower($serverSSLVar) !== 'off') {
            return \true;
        }
        $serverForwarderProtoVar = $this->input->server->getString('HTTP_X_FORWARDED_PROTO', '');
        return !empty($serverForwarderProtoVar) && \strtolower($serverForwarderProtoVar) === 'https';
    }
    /**
     * Static method to return current url of server (without query)
     *
     * @return string
     */
    public static function currentUrl() : string
    {
        $uri = new Uri(self::getInstance()->requestUrl);
        return $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path']);
    }
    /**
     * Static method to return the full request url
     *
     * @return string
     */
    public static function toString() : string
    {
        return self::getInstance()->requestUrl;
    }
    /**
     * Instance of class only used internally
     *
     * @return SystemUri
     */
    private static function getInstance() : \JchOptimize\Core\SystemUri
    {
        if (\is_null(self::$instance)) {
            self::$instance = new \JchOptimize\Core\SystemUri();
        }
        return self::$instance;
    }
    /**
     * Static method to return path to home page script including the host
     *
     * @return string
     */
    public static function baseFull() : string
    {
        return self::getInstance()->baseFull;
    }
    /**
     * Static method to return path to home page script without the host
     *
     * @return string
     */
    public static function basePath() : string
    {
        return self::getInstance()->basePath;
    }
    /**
     * Returns path to script including host based on current request
     *
     * @return string
     */
    public static function currentBaseFull() : string
    {
        return self::getInstance()->currentBaseFull;
    }
    /**
     * Returns path to script excluding host based on current request
     *
     * @return string
     */
    public static function currentBasePath() : string
    {
        return self::getInstance()->currentBasePath;
    }
}
