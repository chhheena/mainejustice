<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare (strict_types=1);
namespace _JchOptimizeVendor\Laminas\Serializer\Adapter;

use _JchOptimizeVendor\Laminas\Serializer\Exception;
use _JchOptimizeVendor\Laminas\Stdlib\ErrorHandler;
use Traversable;
use function extension_loaded;
class IgBinary extends AbstractAdapter
{
    /** @var string Serialized null value */
    private static $serializedNull;
    /**
     * @throws Exception\ExtensionNotLoadedException If igbinary extension is not present.
     * @param array|Traversable|AdapterOptions $options
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('igbinary')) {
            throw new Exception\ExtensionNotLoadedException('PHP extension "igbinary" is required for this adapter');
        }
        if (static::$serializedNull === null) {
            static::$serializedNull = \igbinary_serialize(null);
        }
        parent::__construct($options);
    }
    /**
     * Serialize PHP value to igbinary
     *
     * @param  mixed $value
     * @return string
     * @throws Exception\RuntimeException On igbinary error.
     */
    public function serialize($value)
    {
        ErrorHandler::start();
        $ret = \igbinary_serialize($value);
        $err = ErrorHandler::stop();
        if ($ret === \false) {
            throw new Exception\RuntimeException('Serialization failed', 0, $err);
        }
        return $ret;
    }
    /**
     * Deserialize igbinary string to PHP value
     *
     * @param  string $serialized
     * @return mixed
     * @throws Exception\RuntimeException On igbinary error.
     */
    public function unserialize($serialized)
    {
        if ($serialized === static::$serializedNull) {
            return;
        }
        ErrorHandler::start();
        $ret = \igbinary_unserialize($serialized);
        $err = ErrorHandler::stop();
        if ($ret === null) {
            throw new Exception\RuntimeException('Unserialization failed', 0, $err);
        }
        return $ret;
    }
}
