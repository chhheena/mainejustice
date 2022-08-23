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
namespace JchOptimize\Model;

use JchOptimize\Helper\CacheCleaner;
/**
 * Used in Models that are Database and State aware to save the state to the database
 */
trait SaveSettingsTrait
{
    private function saveSettings()
    {
        $db = $this->db;
        $data = $this->state->toString();
        $sql = $db->getQuery(\true)->update($db->qn('#__extensions'))->set($db->qn('params') . ' = ' . $db->q($data))->where($db->qn('element') . ' = ' . $db->q('com_jchoptimize'))->where($db->qn('type') . ' = ' . $db->q('component'));
        $db->setQuery($sql);
        try {
            $db->execute();
            // The component parameters are cached. We just changed them. Therefore, we MUST reset the system cache which holds them.
            CacheCleaner::clearCacheGroups(['_system'], [1]);
        } catch (\Exception $e) {
            // Don't sweat if it fails
        }
    }
}
