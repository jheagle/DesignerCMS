<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

/**
 * Class CacheRegistry
 *
 * @package Core\Adaptors\Vendor\CacheRegistry
 *
 * @property FilesystemAdapter $classInstance
 */
class CacheRegistry extends Adaptor
{
    /**
     * Clear all cached data, optional prefix for namespaced cache.
     *
     * @param string $prefix
     *
     * @return bool
     */
    public function clear(string $prefix = ''): bool
    {
        return $this->classInstance->clear($prefix);
    }

    /**
     * Remove cached data by key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function delete(string $key): mixed
    {
        return $this->useThrowable(fn() => $this->classInstance->delete($key));
    }

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
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null): mixed
    {
        return $this->useThrowable(
            fn() => $this->classInstance->get(
                $key,
                fn(ItemInterface $item) => $callback(CacheItem::wrapCast($item)),
                $beta,
                $metadata
            )
        );
    }

    /**
     * Reset all static properties and clear the cache.
     *
     * @param string $cacheToken
     *
     * @return static
     *
     * @throws Throwable
     */
    public static function reset(string $cacheToken = ''): static
    {
        if (empty($cacheToken)) {
            self::singleton()->clear();
        } else {
            self::singleton()->delete($cacheToken);
        }
        return parent::reset();
    }
}