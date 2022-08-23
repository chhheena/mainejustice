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

use JchOptimize\Platform\Paths;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Apcu;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\BlackHole;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Memcached;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Redis;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\WinCache;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\InvokableFactory;
class CachingConfigurationProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share('config', function () {
            $app = Factory::getApplication();
            return ['caches' => ['filesystem' => ['name' => 'filesystem', 'options' => ['cache_dir' => Paths::cacheDir(), 'dir_level' => 2, 'dir_permission' => 0755, 'file_permission' => 0644], 'plugins' => [['name' => 'serializer'], ['name' => 'optimizebyfactor', 'options' => ['optimizing_factor' => 100]]]], 'memcached' => ['name' => 'memcached', 'options' => ['servers' => [[(string) $app->get('memcached_server_host', '127.0.0.1'), (int) $app->get('memcached_server_port', 11211)]]], 'plugins' => []], 'apcu' => ['name' => 'apcu', 'options' => [], 'plugins' => []], 'wincache' => ['name' => 'wincache', 'options' => [], 'plugins' => []], 'redis' => ['name' => 'redis', 'options' => ['server' => ['host' => (string) $app->get('redis_server_host', '127.0.0.1'), 'port' => (int) $app->get('redis_server_port', 6379)], 'password' => (string) $app->get('redis_server_auth', ''), 'database' => (int) $app->get('redis_server_db', 0)], 'plugins' => [['name' => 'serializer']]], 'blackhole' => ['name' => 'blackhole', 'options' => [], 'plugins' => []]], 'dependencies' => ['factories' => [Filesystem::class => InvokableFactory::class, Memcached::class => InvokableFactory::class, Apcu::class => InvokableFactory::class, Redis::class => InvokableFactory::class, WinCache::class => InvokableFactory::class, BlackHole::class => InvokableFactory::class], 'aliases' => ['filesystem' => Filesystem::class, 'memcached' => Memcached::class, 'apcu' => Apcu::class, 'redis' => Redis::class, 'wincache' => WinCache::class, 'blackhole' => BlackHole::class]]];
        }, \true);
    }
}
