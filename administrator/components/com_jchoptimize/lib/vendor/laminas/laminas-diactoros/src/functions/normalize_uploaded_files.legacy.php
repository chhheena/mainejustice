<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Zend\Diactoros;

use Psr\Http\Message\UploadedFileInterface;
use function _JchOptimizeVendor\Laminas\Diactoros\normalizeUploadedFiles as laminas_normalizeUploadedFiles;
/**
 * @deprecated Use Laminas\Diactoros\normalizeUploadedFiles instead
 */
function normalizeUploadedFiles(array $files) : array
{
    return laminas_normalizeUploadedFiles(...\func_get_args());
}
