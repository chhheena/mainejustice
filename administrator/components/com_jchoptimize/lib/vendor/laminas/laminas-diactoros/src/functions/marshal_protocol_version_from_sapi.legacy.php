<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Zend\Diactoros;

use function _JchOptimizeVendor\Laminas\Diactoros\marshalProtocolVersionFromSapi as laminas_marshalProtocolVersionFromSapi;
/**
 * @deprecated Use Laminas\Diactoros\marshalProtocolVersionFromSapi instead
 */
function marshalProtocolVersionFromSapi(array $server) : string
{
    return laminas_marshalProtocolVersionFromSapi(...\func_get_args());
}
