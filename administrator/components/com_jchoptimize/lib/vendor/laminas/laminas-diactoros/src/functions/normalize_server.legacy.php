<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Zend\Diactoros;

use function _JchOptimizeVendor\Laminas\Diactoros\normalizeServer as laminas_normalizeServer;
/**
 * @deprecated Use Laminas\Diactoros\normalizeServer instead
 */
function normalizeServer(array $server, callable $apacheRequestHeaderCallback = null) : array
{
    return laminas_normalizeServer(...\func_get_args());
}
