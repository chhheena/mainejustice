<?php

/**
 * @package     JchOptimize\Core
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
namespace JchOptimize\Core;

trait SerializableTrait
{
    public function __serialize()
    {
        return $this->serializedArray();
    }
    public function serialize()
    {
        return \json_encode($this->serializedArray());
    }
    private function serializedArray() : array
    {
        return ['params' => $this->params->jsonSerialize(), 'version' => JCH_VERSION];
    }
    public function __unserialize($data)
    {
        return $this->unserialize($data);
    }
    public function unserialize($data)
    {
        return \json_decode($data);
    }
}
