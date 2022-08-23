<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Platform;

use Exception;
use JchOptimize\Core\Interfaces\Hooks as HooksInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
class Hooks implements HooksInterface
{
    /**
     * @inheritDoc
     */
    public static function onPageCacheSetCaching() : bool
    {
        $results = \true;
        try {
            $results = Factory::getApplication()->triggerEvent('onPageCacheSetCaching');
        } catch (Exception $e) {
        }
        return !\in_array(\false, $results, \true);
    }
    /**
     * @inheritDoc
     */
    public static function onPageCacheGetKey(array $parts) : array
    {
        try {
            $results = Factory::getApplication()->triggerEvent('onPageCacheGetKey');
        } catch (Exception $e) {
        }
        if (!empty($results)) {
            $parts = \array_merge($parts, $results);
        }
        return $parts;
    }
    public static function onUserPostForm() : void
    {
        try {
            // Import the user plugin group.
            PluginHelper::importPlugin('user');
            Factory::getApplication()->triggerEvent('onUserPostForm');
        } catch (Exception $e) {
        }
    }
    public static function onUserPostFormDeleteCookie() : void
    {
        try {
            // Import the user plugin group.
            PluginHelper::importPlugin('user');
            Factory::getApplication()->triggerEvent('onUserPostFormDeleteCookie');
        } catch (Exception $e) {
        }
    }
}
