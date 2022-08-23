<?php

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Laminas\Cache\Storage\PluginManager;
use Psr\Container\ContainerInterface;
final class StoragePluginManagerFactory
{
    public function __invoke(ContainerInterface $container) : PluginManager
    {
        return new PluginManager($container);
    }
}
