<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Laminas\ServiceManager\Exception;

use _JchOptimizeVendor\Interop\Container\Exception\NotFoundException;
use InvalidArgumentException as SplInvalidArgumentException;
/**
 * This exception is thrown when the service locator do not manage to find a
 * valid factory to create a service
 */
class ServiceNotFoundException extends SplInvalidArgumentException implements ExceptionInterface, NotFoundException
{
}
