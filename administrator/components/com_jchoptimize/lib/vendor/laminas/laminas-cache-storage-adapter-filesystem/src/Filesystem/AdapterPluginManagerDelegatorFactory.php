<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;

use _JchOptimizeVendor\Interop\Container\ContainerInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;
use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\InvokableFactory;
use function assert;
final class AdapterPluginManagerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback) : AdapterPluginManager
    {
        $pluginManager = $callback();
        assert($pluginManager instanceof AdapterPluginManager);
        $pluginManager->configure(['factories' => [Filesystem::class => InvokableFactory::class], 'aliases' => ['filesystem' => Filesystem::class, 'Filesystem' => Filesystem::class]]);
        return $pluginManager;
    }
}
