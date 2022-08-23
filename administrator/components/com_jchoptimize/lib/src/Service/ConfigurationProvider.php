<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;
class ConfigurationProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share('params', function ($container) : Registry {
            $db = $container->get('db');
            $sql = $db->getQuery(\true)->select($db->qn('params'))->from($db->qn('#__extensions'))->where($db->qn('type') . " = " . $db->q('component'))->where($db->qn('element') . " = " . $db->q('com_jchoptimize'));
            $json = $db->setQuery($sql)->loadResult();
            $params = new Registry($json);
            if (!\defined('JCH_DEBUG')) {
                \define('JCH_DEBUG', $params->get('debug', 0));
            }
            return $params;
        }, \true);
    }
}
