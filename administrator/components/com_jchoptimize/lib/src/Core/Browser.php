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
namespace JchOptimize\Core;

\defined('_JCH_EXEC') or die('Restricted access');
use JchOptimize\Platform\Utility;
class Browser
{
    protected static $instances = array();
    /**
     * @var \stdClass $oClient
     */
    protected $oClient;
    public function __construct($userAgent)
    {
        $this->oClient = Utility::userAgent($userAgent);
    }
    public static function getInstance($userAgent = '')
    {
        if ($userAgent == '' && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = \trim($_SERVER['HTTP_USER_AGENT']);
        }
        $signature = \md5($userAgent);
        if (!isset(self::$instances[$signature])) {
            self::$instances[$signature] = new \JchOptimize\Core\Browser($userAgent);
        }
        return self::$instances[$signature];
    }
    public function getBrowser()
    {
        return $this->oClient->browser;
    }
    public function getVersion()
    {
        return $this->oClient->browserVersion;
    }
}
