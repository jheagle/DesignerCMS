<?php
/**
 * Based on the function passed in, reduce its parameters with the provided $args. Return the result of evaluation
 * the function or a new function with less arguments.
 *
 * @param callable|string $fn A string function name or function to be curried
 * @param string|object $class Optional class name for the function
 *
 * @return callable
 */
function curry($fn, $class = __CLASS__): callable
{
    /**
     * Take some $args to be applied to the function, either return the evaluated function or a new version of the
     * function with some $args applied here
     *
     * @param mixed ...$args The known function arguments to be re-used
     *
     * @return \Closure|mixed
     */
    return !$class
        ? function (...$args) use ($fn) {
            return count($args) >= (new \ReflectionFunction($fn))->getNumberOfRequiredParameters()
                ? call_user_func_array($fn, $args)
                : function (...$a) use ($fn, $args) {
                    return call_user_func_array(curry($fn), array_merge($args, $a));
                };
        }
        : function (...$args) use ($fn, $class) {
            return count($args) >= (new \ReflectionMethod($class, $fn))->getNumberOfRequiredParameters()
                ? call_user_func_array([$class, $fn], $args)
                : function (...$a) use ($fn, $class, $args) {
                    return call_user_func_array(curry($fn, $class), array_merge($args, $a));
                };
        };
}

$curry = function (...$args) {
    return curry(...$args);
};

if ($declareGlobal ?? false && !array_key_exists('curry', $GLOBALS)) {
    $GLOBALS['curry'] = $curry;
}
