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

use JchOptimize\Database\Database;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
class DatabaseProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias('db', DatabaseInterface::class)->share(DatabaseInterface::class, function () {
            if (\version_compare(JVERSION, '4.0', '>')) {
                return Factory::getContainer()->get('db');
            } else {
                return new Database(Factory::getDbo());
            }
        }, \true);
    }
}
