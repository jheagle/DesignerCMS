<?php

namespace Core\Adaptors\Vendor\Curl;

use Core\Adaptors\Adaptor;

interface HandlerStackInterface
{
    /**
     * Initialize this class with the provided handler.
     *
     * @param Mocks\Handler|Adaptor $build
     *
     * @return Adaptor|HandlerStack
     */
    public static function create(Mocks\Handler|Adaptor $build): Adaptor|HandlerStack;
}