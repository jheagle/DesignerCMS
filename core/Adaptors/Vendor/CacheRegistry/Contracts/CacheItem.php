<?php

namespace Core\Adaptors\Vendor\CacheRegistry\Contracts;

use Core\Adaptors\Adaptor;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class CacheItem
 *
 * @package Core\Adaptors\Vendor\CacheRegistry\Contracts
 *
 * @property ItemInterface $classInstance
 */
abstract class CacheItem extends Adaptor
{
}