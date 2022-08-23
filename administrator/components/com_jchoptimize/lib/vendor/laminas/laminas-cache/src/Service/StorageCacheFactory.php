<?php

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use _JchOptimizeVendor\Webmozart\Assert\Assert;
use function assert;
final class StorageCacheFactory
{
    public const CACHE_CONFIGURATION_KEY = 'cache';
    public function __invoke(ContainerInterface $container) : StorageInterface
    {
        $config = $container->get('config');
        Assert::isArrayAccessible($config);
        $cacheConfig = $config['cache'] ?? [];
        Assert::isMap($cacheConfig);
        $factory = $container->get(StorageAdapterFactoryInterface::class);
        assert($factory instanceof StorageAdapterFactoryInterface);
        $factory->assertValidConfigurationStructure($cacheConfig);
        return $factory->createFromArrayConfiguration($cacheConfig);
    }
}
