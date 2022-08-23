<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\LaminasBench\Cache;

use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Benchmark\AbstractStorageAdapterBenchmark;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;
use _JchOptimizeVendor\PhpBench\Benchmark\Metadata\Annotations\Iterations;
use _JchOptimizeVendor\PhpBench\Benchmark\Metadata\Annotations\Revs;
use _JchOptimizeVendor\PhpBench\Benchmark\Metadata\Annotations\Warmup;
/**
 * @Revs(100)
 * @Iterations(10)
 * @Warmup(1)
 */
class FilesystemStorageAdapterBench extends AbstractStorageAdapterBenchmark
{
    public function __construct()
    {
        parent::__construct(new Filesystem());
    }
}
