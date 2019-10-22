<?php

/**
 * Pass in a few methods to be run in sequence, returns a function expecting the data which will be altered by the
 * sequence.
 *
 * @param callable[] ...$fns All of the functions receiving the same parameter
 *
 * @return \Closure
 */
$pipe = static function (...$fns): callable {
    /**
     * Pass $data to each of the $fns provided along with any mutations it receives.
     *
     * @param mixed $data The data to have each function applied to.
     *
     * @return mixed
     */
    return function ($data) use ($fns) {
        /**
         * Type def
         *
         * @var bool $cancelApply This flag is set when a function has received it by reference and within the
         * function it may cancel executing subsequent pipe functions
         */
        $cancelApply = false;
        return array_reduce($fns, function ($data, callable $f) use (&$cancelApply) {
            return $cancelApply ? $data : $f($data, $cancelApply);
        }, $data);
    };
};

if ($declareGlobal ?? false && !function_exists('pipe')) {
    $GLOBALS['pipe'] = $pipe;
    function pipe(...$fns): callable
    {
        return $GLOBALS['pipe'](...$fns);
    }
}
