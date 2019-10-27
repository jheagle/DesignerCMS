<?php
if (!function_exists('requiredParameterCount')) {
    /**
     * @param callable|string|array $fn
     *
     * @return int
     */
    function requiredParameterCount($fn): int
    {
        static $magicMethodClasses = [
            \Core\Utilities\Functional\Pure::class,
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
    }
}

$requiredParameterCount = static function ($fn): int {
    return requiredParameterCount($fn);
};
