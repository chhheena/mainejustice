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

\defined('_JEXEC') or \dir('Restricted Access');
use JchOptimize\Helper\CacheCleaner;
use JchOptimize\Core\Admin\Tasks;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelInterface;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
class ModeSwitcher implements DatabaseModelInterface, StatefulModelInterface
{
    use DatabaseModelTrait;
    use StatefulModelTrait;
    /**
     * @var StorageInterface
     */
    private $cacheModel;
    public function __construct(Registry $params, \JchOptimize\Model\Cache $cacheModel)
    {
        $this->cacheModel = $cacheModel;
        $this->setState($params);
    }
    public function setProduction()
    {
        $this->togglePluginState('jchoptimize');
        if ($this->state->get('pro_page_cache_integration_enable', '0')) {
            $this->togglePluginState('jchoptimizepagecache');
        }
        Tasks::generateNewCacheKey();
    }
    public function togglePluginState($element, $bEnable = \true)
    {
        $db = $this->db;
        $query = $db->getQuery(\true)->update('#__extensions')->set($db->quoteName('enabled') . ' = ' . ($bEnable ? '1' : '0'))->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))->where($db->quoteName('folder') . ' = ' . $db->quote('system'))->where($db->quoteName('element') . ' = ' . $db->quote($element));
        $db->setQuery($query);
        $db->execute();
        CacheCleaner::clearPluginsCache();
    }
    public function setDevelopment()
    {
        $this->togglePluginState('jchoptimize', \false);
        if ($this->state->get('pro_page_cache_integration_enable', '0')) {
            $this->togglePluginState('jchoptimizepagecache', \false);
        }
        $this->cacheModel->cleanCache();
    }
}
