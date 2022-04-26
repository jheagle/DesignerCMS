<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

use Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException;
use Core\Contracts\Adaptable;
use Throwable;

interface CacheRegistryInterface
{
    /**
     * Clear all cached data, optional prefix for namespaced cache.
     *
     * @param string $prefix
     *
     * @return bool
     */
    public function clear(string $prefix = ''): bool;

    /**
     * Remove cached data by key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function delete(string $key): mixed;

    /**
     * Retrieve data which was previously cached by key.
     *
     * @param string $key
     * @param callable $callback
     * @param float|null $beta
     * @param array|null $metadata
     *
     * @return mixed
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function get(string $key, callable $callback, float $beta = null, array $metadata = null): mixed;

    /**
     * Reset all static properties and clear the cache.
     *
     * @param string $cacheToken
     *
     * @return static
     *
     * @throws InvalidArgumentException|Throwable
     */
    public static function reset(string $cacheToken = ''): Adaptable;
}