<?php

namespace Core\Utilities\Functional;

/**
 * Class Pure
 *
 * @package Core\Utilities\Functional
 */
class Pure
{
    use PureTrait;

    const RESET_FUNCTIONS = 'reset_functions';

    private static $functions = [];

    /**
     * @param string $name
     * @param array|mixed $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (array_key_exists($name, static::$functions)) {
            $functions = static::$functions;
            return $functions[$name](...$arguments);
        }
        static::$functions = static::importFunctions();
        $functions = static::$functions;
        return array_key_exists($name, static::$functions)
            ? $functions[$name](...$arguments)
            : false;
    }
}