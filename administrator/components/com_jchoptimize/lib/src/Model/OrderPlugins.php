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
namespace JchOptimize\Model;

\defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelInterface;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelTrait;
use Joomla\Utilities\ArrayHelper;
class OrderPlugins implements DatabaseModelInterface
{
    use DatabaseModelTrait;
    public function orderPlugins()
    {
        //These plugins must be ordered last in this order; array of plugin elements
        $aOrder = array('jscsscontrol', 'eorisis_jquery', 'jqueryeasy', 'quix', 'jchoptimize', 'setcanonical', 'canonical', 'plugin_googlemap3', 'jomcdn', 'cdnforjoomla', 'bigshotgoogleanalytics', 'GoogleAnalytics', 'pixanalytic', 'ykhoonhtmlprotector', 'jat3', 'cache', 'plg_gkcache', 'pagecacheextended', 'homepagecache', 'jSGCache', 'j2pagecache', 'jotcache', 'lscache', 'vmcache_last', 'pixcookiesrestrict', 'speedcache', 'speedcache_last', 'jchoptimizepagecache');
        //Get an associative array of all installed system plugins with their extension id, ordering, and element
        $aPlugins = self::getPlugins();
        //Get an array of all the plugins that are installed that are in the array of specified plugin order above
        $aLowerPlugins = \array_values(\array_filter($aOrder, function ($aVal) use($aPlugins) {
            return \array_key_exists($aVal, $aPlugins);
        }));
        //Number of installed plugins
        $iNoPlugins = \count($aPlugins);
        //Number of installed plugins that needs to be ordered at the bottom of the order
        $iNoLowerPlugins = \count($aLowerPlugins);
        $iBaseOrder = $iNoPlugins - $iNoLowerPlugins;
        $cid = array();
        $order = array();
        //Iterate through list of installed system plugins
        foreach ($aPlugins as $key => $value) {
            if (\in_array($key, $aLowerPlugins)) {
                $value['ordering'] = $iNoPlugins + 1 + \array_search($key, $aLowerPlugins);
            }
            $cid[] = $value['extension_id'];
            $order[] = $value['ordering'];
        }
        ArrayHelper::toInteger($cid);
        ArrayHelper::toInteger($order);
        $config = ['base_path' => JPATH_ADMINISTRATOR . '/components/com_plugins', 'name' => 'plugins'];
        //Joomla version 3.9 doesn't use a factory
        if (\version_compare(JVERSION, '3.10', 'lt')) {
            $oPluginsController = new BaseController($config);
        } else {
            $factory = \version_compare(JVERSION, '3.999.999', 'gt') ? new MVCFactory('\\Joomla\\Component\\Plugins') : new LegacyFactory();
            $oPluginsController = new BaseController($config, $factory);
        }
        $oPluginModel = $oPluginsController->getModel('Plugin', '', $config);
        return $oPluginModel->saveorder($cid, $order);
    }
    private function getPlugins()
    {
        $db = $this->db;
        $oQuery = $db->getQuery(\true);
        $oQuery->select($db->quoteName(array('extension_id', 'ordering', 'element')))->from($db->quoteName('#__extensions'))->where(array($db->quoteName('type') . ' = ' . $db->quote('plugin'), $db->quoteName('folder') . ' = ' . $db->quote('system')), 'AND');
        $db->setQuery($oQuery);
        return $db->loadAssocList('element');
    }
}
