<?php

namespace Core\Adaptors\Vendor\Curl;

use Core\Adaptors\Adaptor;
use Core\Contracts\Adaptable;

/**
 * Class HandlerStack
 *
 * @package Core\Adaptors\Vendor\Curl
 *
 * @property \GuzzleHttp\HandlerStack $classInstance
 */
class HandlerStack extends Adaptor implements HandlerStackInterface
{
    /**
     * Initialize this class with the provided handler.
     *
     * @param Mocks\Handler|Adaptor $build
     *
     * @return Adaptable|HandlerStack
     */
    public static function create(Mocks\Handler|Adaptor $build): Adaptor|HandlerStack
    {
        return self::initialize(self::initialize()->classInstance::create($build))->build();
    }
}