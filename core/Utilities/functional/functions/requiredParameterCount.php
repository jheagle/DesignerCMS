<?php

use Core\Utilities\Functional\Pure;

if (!function_exists('requiredParameterCount')) {
    /**
     * Given a function, detect the number of required parameters the function has. Use an array of class name and
     * method name for methods.
     *
     * @param callable|array|string $fn
     *
     * @return int
     */
    function requiredParameterCount(callable|array|string $fn): int
    {
        static $magicMethodClasses = [
            Pure::class,
        ];
        if (is_array($fn) && count((array)$fn) > 1 && in_array($fn[0], $magicMethodClasses)) {
            $fn = $fn[0]::getFunction($fn[1]);
        }
        try {
            return (is_array($fn) && count((array)$fn) > 1)
                ? (new ReflectionMethod($fn[0], $fn[1]))->getNumberOfRequiredParameters()
                : (new ReflectionFunction($fn))->getNumberOfRequiredParameters();
        } catch (ReflectionException $e) {
            echo $e;
        }
        return 0;
    }
}

$requiredParameterCount = static function ($fn): int {
    return requiredParameterCount($fn);
};

if (($declareGlobal ?? false) && ($GLOBALS['requiredParameterCount'] ?? false)) {
    $GLOBALS['requiredParameterCount'] = $requiredParameterCount;
}
