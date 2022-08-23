<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */
namespace _JchOptimizeVendor\Interop\Container\Exception;

use Psr\Container\NotFoundExceptionInterface as PsrNotFoundException;
/**
 * No entry was found in the container.
 */
interface NotFoundException extends ContainerException, PsrNotFoundException
{
}
