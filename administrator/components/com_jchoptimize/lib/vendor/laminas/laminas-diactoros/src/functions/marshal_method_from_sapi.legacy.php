<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Zend\Diactoros;

use function _JchOptimizeVendor\Laminas\Diactoros\marshalMethodFromSapi as laminas_marshalMethodFromSapi;
/**
 * @deprecated Use Laminas\Diactoros\marshalMethodFromSapi instead
 */
function marshalMethodFromSapi(array $server) : string
{
    return laminas_marshalMethodFromSapi(...\func_get_args());
}
