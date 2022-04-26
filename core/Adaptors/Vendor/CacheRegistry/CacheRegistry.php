<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException;
use Core\Contracts\Adaptable;
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
class CacheRegistry extends Adaptor implements CacheRegistryInterface
{
    /**
     * Clear all cached data, optional prefix for namespaced cache.
     *
     * @param string $prefix
     *
     * @return bool
     */
    final public function clear(string $prefix = ''): bool
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
    final public function delete(string $key): mixed
    {
        return $this->useThrowable(
            fn() => $this->classInstance->delete($key)
        );
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
    final public function get(string $key, callable $callback, float $beta = null, array $metadata = null): mixed
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
     * @return Adaptable
     *
     * @throws InvalidArgumentException|Throwable
     */
    public static function reset(string $cacheToken = ''): Adaptable
    {
        $className = get_called_class();
        self::$customInstances[$className] = null;
        self::$staticInstances[$className] = null;
        $instance = self::singleton();
        if (empty($cacheToken)) {
            $instance->clear();
            return $instance;
        }
        $instance->delete($cacheToken);
        return $instance;
    }
}