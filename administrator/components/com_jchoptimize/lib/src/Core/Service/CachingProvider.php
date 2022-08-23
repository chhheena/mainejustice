<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\Service;

use JchOptimize\Core\Exception;
use JchOptimize\Core\Plugin\ClearExpiredByFactor;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use _JchOptimizeVendor\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\Laminas\Cache\Pattern\CallbackCache;
use _JchOptimizeVendor\Laminas\Cache\Pattern\CaptureCache;
use _JchOptimizeVendor\Laminas\Cache\Pattern\PatternOptions;
use _JchOptimizeVendor\Laminas\Cache\Service\StorageAdapterFactory;
use _JchOptimizeVendor\Laminas\Cache\Service\StorageAdapterFactoryInterface;
use _JchOptimizeVendor\Laminas\Cache\Service\StorageCacheAbstractServiceFactory;
use _JchOptimizeVendor\Laminas\Cache\Service\StoragePluginFactory;
use _JchOptimizeVendor\Laminas\Cache\Service\StoragePluginFactoryInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Apcu;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Memcached;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Redis;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\WinCache;
use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Laminas\Cache\Storage\IterableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\PluginAwareInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\PluginManager;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\PluginManagerInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\PsrContainerDecorator;
use Psr\Log\LoggerInterface;
use function file_exists;
use function max;
use function md5;
class CachingProvider implements ServiceProviderInterface
{
    public function __construct()
    {
        if (!file_exists(Paths::cacheDir())) {
            Folder::create(Paths::cacheDir());
        }
    }
    public function register(Container $container)
    {
        $container->alias(StorageAdapterFactoryInterface::class, StorageAdapterFactory::class)->share(StorageAdapterFactory::class, [$this, 'getStorageAdapterFactoryService'], \true);
        $container->alias(PluginManagerInterface::class, AdapterPluginManager::class)->share(AdapterPluginManager::class, [$this, 'getAdapterPluginManagerService'], \true);
        $container->alias(StoragePluginFactoryInterface::class, StoragePluginFactory::class)->share(StoragePluginFactory::class, [$this, 'getStoragePluginFactoryService'], \true);
        $container->share(PluginManager::class, [$this, 'getPluginManagerService'], \true);
        $container->share(StorageInterface::class, [$this, 'getStorageInterfaceService'], \true);
        $container->share(CallbackCache::class, [$this, 'getCallbackCacheService'], \true);
        $container->share(CaptureCache::class, [$this, 'getCaptureCacheService'], \true);
        $container->share('page_cache', [$this, 'getPageCacheService'], \true);
        $container->alias('Filesystem', Filesystem::class)->share(Filesystem::class, [$this, 'getFilesystemService'], \true);
        $container->alias('Redis', Redis::class)->share(Redis::class, [$this, 'getRedisService'], \true);
        $container->alias('Apcu', Apcu::class)->share(Apcu::class, [$this, 'getApcuService'], \true);
        $container->alias('Memcached', Memcached::class)->share(Memcached::class, [$this, 'getMemcachedService'], \true);
        $container->alias('WinCache', WinCache::class)->share(WinCache::class, [$this, 'getWinCacheService'], \true);
        $container->share(TaggableInterface::class, [$this, 'getTaggableInterfaceService'], \true);
    }
    public function getStorageAdapterFactoryService(Container $container) : StorageAdapterFactoryInterface
    {
        return new StorageAdapterFactory($container->get(PluginManagerInterface::class), $container->get(StoragePluginFactoryInterface::class));
    }
    public function getAdapterPluginManagerService(Container $container) : PluginManagerInterface
    {
        return new AdapterPluginManager($container, $container->get('config')['dependencies']);
    }
    /**
     * This will always fetch the Filesystem storage adapter
     *
     * @throws Exception\RuntimeException
     */
    public function getFilesystemService(Container $container) : StorageInterface
    {
        $fsCache = $this->getCacheAdapter($container, 'filesystem');
        $fsCache->getOptions()->setTtl(0);
        return $fsCache;
    }
    /**
     * @throws Exception\RuntimeException
     */
    public function getRedisService(Container $container) : StorageInterface
    {
        $redisCache = $this->getCacheAdapter($container, 'redis');
        $redisCache->getOptions()->setTtl(0);
        return $redisCache;
    }
    /**
     * @throws Exception\RuntimeException
     */
    public function getApcuService(Container $container) : StorageInterface
    {
        $apcuCache = $this->getCacheAdapter($container, 'apcu');
        $apcuCache->getOptions()->setTtl(0);
        return $apcuCache;
    }
    /**
     * @throws Exception\RuntimeException
     */
    public function getMemcachedService(Container $container) : StorageInterface
    {
        $memcachedCache = $this->getCacheAdapter($container, 'memcached');
        $memcachedCache->getOptions()->setTtl(0);
        return $memcachedCache;
    }
    /**
     * @throws Exception\RuntimeException
     */
    public function getWinCacheService(Container $container) : StorageInterface
    {
        $winCacheCache = $this->getCacheAdapter($container, 'wincache');
        $winCacheCache->getOptions()->setTtl(0);
        return $winCacheCache;
    }
    /**
     * @param   Container  $container
     * @param              $adapter
     *
     * @return StorageInterface
     * @throws Exception\RuntimeException
     */
    private function getCacheAdapter(Container $container, $adapter) : StorageInterface
    {
        //Use whichever lifetime is greater to ensure page cache expires before
        $pageCacheTtl = $container->get('params')->get('page_cache_lifetime', '900');
        $globalTtl = $container->get('params')->get('cache_lifetime', '900');
        $lifetime = max($pageCacheTtl, $globalTtl);
        try {
            $factory = new StorageCacheAbstractServiceFactory();
            /** @var StorageInterface $cache */
            $cache = $factory(new PsrContainerDecorator($container), $adapter);
            $cache->getOptions()->setNamespace('jchoptimizecache')->setTtl($lifetime);
            if ($cache instanceof PluginAwareInterface) {
                $plugin = (new ClearExpiredByFactor())->setContainer($container);
                $plugin->setLogger($container->get(LoggerInterface::class));
                $plugin->getOptions()->setClearingFactor(100);
                $cache->addPlugin($plugin);
            }
            //Let's make sure we can connect
            $cache->addItem(md5('__ITEM__'), '__ITEM__');
            return $cache;
        } catch (ExceptionInterface $e) {
            $logger = $container->get(LoggerInterface::class);
            $message = 'Error retrieving configured storage adapter with message: ' . $e->getMessage();
            if ($adapter != 'filesystem') {
                $message .= ': Using the filesystem storage instead';
            }
            $logger->error($message);
            Utility::publishAdminMessages($message, 'error');
            if ($adapter != 'filesystem') {
                return $container->get(FileSystem::class);
            }
            throw new Exception\RuntimeException($message);
        }
    }
    public function getStoragePluginFactoryService(Container $container) : StoragePluginFactoryInterface
    {
        return new StoragePluginFactory($container->get(PluginManager::class));
    }
    public function getPluginManagerService(Container $container) : PluginManagerInterface
    {
        return new PluginManager($container, $container->get('config')['dependencies']);
    }
    /**
     * This will get the storage adapter that is configured in the plugin parameters
     *
     * @param   Container  $container
     *
     * @return StorageInterface
     * @throws Exception\RuntimeException
     */
    public function getStorageInterfaceService(Container $container) : StorageInterface
    {
        return $this->getCacheAdapter($container, Utility::getCacheStorage($container->get('params')));
    }
    public function getCallbackCacheService(Container $container) : CallbackCache
    {
        return new CallbackCache($container->get(StorageInterface::class), new PatternOptions(['cache_output' => \false]));
    }
    public function getCaptureCacheService(Container $container) : CaptureCache
    {
        $publicDir = Paths::captureCacheDir();
        if (!file_exists($publicDir)) {
            $html = <<<HTML
<html><head><title></title></head><body></body></html>';
HTML;
            File::write($publicDir . '/index.html', $html);
            $htaccess = <<<APACHECONFIG
<IfModule mod_autoindex.c>
\tOptions -Indexes
</IfModule>
APACHECONFIG;
            File::write($publicDir . '/.htaccess', $htaccess);
        }
        return new CaptureCache(new PatternOptions(['public_dir' => $publicDir, 'file_locking' => \true, 'file_permission' => 0644, 'dir_permission' => 0755, 'umask' => \false]));
    }
    public function getTaggableInterfaceService(Container $container) : TaggableInterface
    {
        $cache = $container->get(StorageInterface::class);
        if ($cache instanceof TaggableInterface && $cache instanceof IterableInterface) {
            return $cache;
        }
        return $container->get('Filesystem');
    }
    public function getPageCacheService(Container $container) : ?StorageInterface
    {
        try {
            $factory = new StorageCacheAbstractServiceFactory();
            /** @var StorageInterface $cache */
            $cache = $factory(new PsrContainerDecorator($container), Utility::getCacheStorage($container->get('params')));
            $cache->getOptions()->setNamespace('jchoptimizepagecache')->setTtl($container->get('params')->get('page_cache_lifetime', '900'));
        } catch (ExceptionInterface $e) {
            //Exception will be handled with storage cache
            return null;
        }
        return $cache;
    }
}
