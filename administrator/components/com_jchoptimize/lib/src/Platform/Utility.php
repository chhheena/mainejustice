<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Platform;

\defined('_JEXEC') or die('Restricted access');
use Exception;
use JchOptimize\Core\Interfaces\Utility as UtilityInterface;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
class Utility implements UtilityInterface
{
    /**
     * @param   string  $text
     *
     * @return string
     */
    public static function translate(string $text) : string
    {
        if (\strlen($text) > 20) {
            $text = \substr($text, 0, \strpos(\wordwrap($text, 20), "\n"));
        }
        $text = 'COM_JCHOPTIMIZE_' . \strtoupper(\str_replace([' ', '\''], ['_', ''], $text));
        return Text::_($text);
    }
    /**
     * @return bool
     * @throws \Exception
     */
    public static function isGuest() : bool
    {
        if (\version_compare(JVERSION, '4.0', 'gt')) {
            return Factory::getApplication()->getIdentity()->guest;
        } else {
            return Factory::getUser()->guest;
        }
    }
    /**
     * @param   array  $headers
     */
    public static function sendHeaders($headers)
    {
        if (!empty($headers)) {
            try {
                $app = Factory::getApplication();
                foreach ($headers as $header => $value) {
                    $app->setHeader($header, $value, \true);
                }
            } catch (Exception $e) {
                //Ignore
            }
        }
    }
    public static function userAgent($userAgent) : \stdClass
    {
        $oWebClient = new WebClient($userAgent);
        $oUA = new \stdClass();
        switch ($oWebClient->browser) {
            case $oWebClient::CHROME:
                $oUA->browser = 'Chrome';
                break;
            case $oWebClient::FIREFOX:
                $oUA->browser = 'Firefox';
                break;
            case $oWebClient::SAFARI:
                $oUA->browser = 'Safari';
                break;
            case $oWebClient::EDGE:
                $oUA->browser = 'Edge';
                break;
            case $oWebClient::IE:
                $oUA->browser = 'Internet Explorer';
                break;
            case $oWebClient::OPERA:
                $oUA->browser = 'Opera';
                break;
            default:
                $oUA->browser = 'Unknown';
                break;
        }
        $oUA->browserVersion = $oWebClient->browserVersion;
        switch ($oWebClient->platform) {
            case $oWebClient::ANDROID:
            case $oWebClient::ANDROIDTABLET:
                $oUA->os = 'Android';
                break;
            case $oWebClient::IPAD:
            case $oWebClient::IPHONE:
            case $oWebClient::IPOD:
                $oUA->os = 'iOS';
                break;
            case $oWebClient::MAC:
                $oUA->os = 'Mac';
                break;
            case $oWebClient::WINDOWS:
            case $oWebClient::WINDOWS_CE:
            case $oWebClient::WINDOWS_PHONE:
                $oUA->os = 'Windows';
                break;
            case $oWebClient::LINUX:
                $oUA->os = 'Linux';
                break;
            default:
                $oUA->os = 'Unknown';
                break;
        }
        return $oUA;
    }
    /**
     * Should return the attribute used to store content values for popover that the version of Bootstrap
     * is using
     *
     * @return string
     */
    public static function bsTooltipContentAttribute() : string
    {
        return \version_compare(JVERSION, '3.99.99', '<') ? 'data-content' : 'data-bs-content';
    }
    public static function isPageCacheEnabled(Registry $params, bool $nativeCache = \false) : bool
    {
        return PluginHelper::isEnabled('system', 'jchoptimizepagecache');
    }
    public static function isMobile() : bool
    {
        $webClient = new WebClient();
        return $webClient->mobile;
    }
    public static function getCacheStorage($params) : string
    {
        switch ($params->get('pro_cache_storage_adapter', 'filesystem')) {
            //Used in Unit testing.
            case 'blackhole':
                return 'blackhole';
            case 'global':
                $storageMap = ['file' => 'filesystem', 'redis' => 'redis', 'apcu' => 'apcu', 'memcached' => 'memcached', 'wincache' => 'wincache'];
                $app = Factory::getApplication();
                $handler = $app->get('cache_handler', 'file');
                if (\in_array($handler, \array_keys($storageMap))) {
                    return $storageMap[$handler];
                }
            case 'filesystem':
            default:
                return 'filesystem';
        }
    }
    public static function getHeaders() : array
    {
        try {
            $headers = [];
            foreach (Factory::getApplication()->getHeaders() as $header) {
                $headers[] = $header;
            }
            return $headers;
        } catch (\Exception $e) {
            return [];
        }
    }
    public static function publishAdminMessages($message, $messageType)
    {
        Factory::getApplication()->enqueueMessage($message, $messageType);
    }
    public static function getLogsPath() : string
    {
        return Factory::getApplication()->get('log_path');
    }
    public static function isSiteGzipEnabled() : bool
    {
        return Factory::getApplication()->get('gzip') && !\ini_get('zlib.output_compression') && \ini_get('output_handler') !== 'ob_gzhandler';
    }
    public static function prepareDataFromCache(?array $data) : ?array
    {
        // The following code searches for a token in the cached page and replaces it with the proper token.
        if (isset($data['body'])) {
            $token = Session::getFormToken();
            $search = '#<input type="?hidden"? name="?[\\da-f]{32}"? value="?1"?\\s*/?>#';
            $replacement = '<input type="hidden" name="' . $token . '" value="1">';
            $data['body'] = \preg_replace($search, $replacement, $data['body']);
        }
        return $data;
    }
    public static function outputData(array $data) : void
    {
        $app = Factory::getApplication();
        if (!empty($data['headers'])) {
            foreach ($data['headers'] as $header) {
                $app->setHeader($header['name'], $header['value']);
            }
        }
        $app->setBody($data['body']);
        echo $app->toString((bool) $app->get('gzip'));
        $app->close();
    }
}
