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

use JchOptimize\Container;
use JchOptimize\Core\Interfaces\Plugin as PluginInterface;
use JchOptimize\Helper\CacheCleaner;
use Joomla\Registry\Registry;
\defined('_JEXEC') or die('Restricted access');
class Plugin implements PluginInterface
{
    protected static $plugin = null;
    /**
     *
     * @return integer
     */
    public static function getPluginId()
    {
        $plugin = static::loadjch();
        return $plugin->extension_id;
    }
    /**
     *
     * @return mixed|null
     */
    private static function loadjch()
    {
        if (self::$plugin !== null) {
            return self::$plugin;
        }
        $db = Container::getInstance()->get('db');
        $query = $db->getQuery(\true)->select('folder AS type, element AS name, params, extension_id')->from('#__extensions')->where('type = ' . $db->quote('component'))->where('element = ' . $db->quote('com_jchoptimize'));
        self::$plugin = $db->setQuery($query)->loadObject();
        return self::$plugin;
    }
    /**
     *
     * @return mixed|null
     */
    public static function getPlugin()
    {
        return static::loadjch();
    }
    /**
     * @deprecated
     */
    public static function getPluginParams()
    {
        return Container::getInstance()->get('params');
    }
    /**
     * @param   Registry  $params
     */
    public static function saveSettings(Registry $params)
    {
        $container = Container::getInstance();
        $db = $container->get('db');
        $sql = $db->getQuery(\true)->update($db->qn('#__extensions'))->set($db->qn('params') . ' = ' . $db->q($params->toString()))->where($db->qn('element') . ' = ' . $db->q('com_jchoptimize'))->where($db->qn('type') . ' = ' . $db->q('component'));
        $db->setQuery($sql);
        try {
            $db->execute();
            // The component parameters are cached. We just changed them. Therefore we MUST reset the system cache which holds them.
            CacheCleaner::clearCacheGroups(['_system'], [1]);
        } catch (\Exception $e) {
            // Don't sweat if it fails
        }
    }
}
