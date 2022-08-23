<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Laminas\ServiceManager;

use _JchOptimizeVendor\Interop\Container\Exception\ContainerException;
use _JchOptimizeVendor\Laminas\ServiceManager\Exception\InvalidServiceException;
/**
 * Interface for a plugin manager
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance
     *
     * @param  object $instance
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager.
     * @throws ContainerException If any other error occurs.
     */
    public function validate($instance);
}
