<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

use Core\Adaptors\Adaptor;

/**
 * Class CacheItem
 *
 * @package Core\Adaptors\Vendor\CacheRegistry\Contracts
 *
 * @property \Symfony\Component\Cache\CacheItem $classInstance
 */
class CacheItem extends Adaptor implements CacheItemInterface
{
    /**
     * Set the time when this cache must expire.
     *
     * @param mixed $getExpires
     *
     * @return $this
     */
    final public function expiresAfter(mixed $getExpires): static
    {
        $this->classInstance->expiresAfter($getExpires);
        return $this;
    }
}