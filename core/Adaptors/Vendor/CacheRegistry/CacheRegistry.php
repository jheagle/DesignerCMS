<?php

namespace Core\Adaptors\Vendor\CacheRegistry;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
     * Remove cached data by key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function delete(string $key)
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
        return $this->useThrowable(fn() => $this->classInstance->get($key, $callback, $beta, $metadata));
    }
}