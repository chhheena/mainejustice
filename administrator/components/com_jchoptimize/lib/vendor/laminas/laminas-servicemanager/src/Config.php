<?php

declare (strict_types=1);
namespace _JchOptimizeVendor\Laminas\ServiceManager;

use _JchOptimizeVendor\Laminas\Stdlib\ArrayUtils\MergeRemoveKey;
use _JchOptimizeVendor\Laminas\Stdlib\ArrayUtils\MergeReplaceKeyInterface;
use function array_key_exists;
use function array_keys;
use function is_array;
use function is_int;
/**
 * Object for defining configuration and configuring an existing service manager instance.
 *
 * In order to provide configuration merging capabilities, this class implements
 * the same functionality as `Laminas\Stdlib\ArrayUtils::merge()`. That routine
 * allows developers to specifically shape how values are merged:
 *
 * - A value which is an instance of `MergeRemoveKey` indicates the value should
 *   be removed during merge.
 * - A value that is an instance of `MergeReplaceKeyInterface` indicates that the
 *   value it contains should be used to replace any previous versions.
 *
 * These features are advanced, and not typically used. If you wish to use them,
 * you will need to require the laminas-stdlib package in your application.
 */
class Config implements ConfigInterface
{
    /** @var array */
    private $allowedKeys = ['abstract_factories' => \true, 'aliases' => \true, 'delegators' => \true, 'factories' => \true, 'initializers' => \true, 'invokables' => \true, 'lazy_services' => \true, 'services' => \true, 'shared' => \true];
    /** @var array */
    protected $config = ['abstract_factories' => [], 'aliases' => [], 'delegators' => [], 'factories' => [], 'initializers' => [], 'invokables' => [], 'lazy_services' => [], 'services' => [], 'shared' => []];
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Only merge keys we're interested in
        foreach (array_keys($config) as $key) {
            if (!isset($this->allowedKeys[$key])) {
                unset($config[$key]);
            }
        }
        $this->config = $this->merge($this->config, $config);
    }
    /**
     * @inheritDoc
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        return $serviceManager->configure($this->config);
    }
    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->config;
    }
    /**
     * Copy paste from https://github.com/laminas/laminas-stdlib/commit/26fcc32a358aa08de35625736095cb2fdaced090
     * to keep compatibility with previous version
     *
     * @link https://github.com/zendframework/zend-servicemanager/pull/68
     */
    private function merge(array $a, array $b) : array
    {
        foreach ($b as $key => $value) {
            if ($value instanceof MergeReplaceKeyInterface) {
                $a[$key] = $value->getData();
            } elseif (isset($a[$key]) || array_key_exists($key, $a)) {
                if ($value instanceof MergeRemoveKey) {
                    unset($a[$key]);
                } elseif (is_int($key)) {
                    $a[] = $value;
                } elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = $this->merge($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                if (!$value instanceof MergeRemoveKey) {
                    $a[$key] = $value;
                }
            }
        }
        return $a;
    }
}
