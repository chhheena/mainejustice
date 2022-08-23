<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Zend\Diactoros;

use function _JchOptimizeVendor\Laminas\Diactoros\parseCookieHeader as laminas_parseCookieHeader;
/**
 * @deprecated Use Laminas\Diactoros\parseCookieHeader instead
 */
function parseCookieHeader($cookieHeader) : array
{
    return laminas_parseCookieHeader(...\func_get_args());
}
