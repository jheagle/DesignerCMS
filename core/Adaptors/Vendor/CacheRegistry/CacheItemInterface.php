<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

interface CacheItemInterface
{
    /**
     * Set the time when this cache must expire.
     *
     * @param mixed $getExpires
     *
     * @return $this
     */
    public function expiresAfter(mixed $getExpires): static;
}