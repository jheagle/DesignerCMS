<?php

namespace Core\Utilities\Functional;

class Pure
{
    public static function __callStatic($name, $arguments)
    {
        if (!method_exists(static::class, $name)) {
            include "functions/{$name}.php";
            return call_user_func_array($name, $arguments);
        }
        return call_user_func(static::$$name, $arguments);
    }
}