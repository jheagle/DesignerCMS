<?php
if (!function_exists('rawCurry')) {
    /**
     * Available with the inclusion of Pure.
     * Make any function curried, a simpler version of using curry. Either nest the function as the first argument of
     * curried or save the anonymous function to a variable to use as the parameter. Provide minimum number or arguments
     * required to use the function (where a function having defaulted parameters may have a larger number of total
     * possible parameters, but only a smaller set of parameters are required.
     *
     * @param callable|string|array $fn
     * @param int $minArgs
     * @param array $args
     *
     * @return \Closure
     */
    function rawCurry($fn, int $minArgs = -1, array $args = []): callable
    {
        if ($minArgs < 0) {
            $minArgs = requiredParameterCount($fn);
        }
        return function () use ($fn, $minArgs, $args) {
            $args = array_merge($args, func_get_args());
            return (count($args) < $minArgs)
                ? rawCurry($fn, $minArgs, $args)
                : call_user_func($fn, ...$args);
        };
    }
}

$curry = static function (...$args) {
    return rawCurry(...$args);
};

if ($declareGlobal ?? false && !function_exists('curry')) {
    $GLOBALS['curry'] = $curry;
    function curry($fn): callable
    {
        return $GLOBALS['curry']($fn);
    }
}
