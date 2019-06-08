<?php
/**
 * Pass in a few methods to be run in sequence, returns a function expecting the data which will be altered by the
 * sequence.
 *
 * @param callable[] ...$fns All of the functions receiving the same parameter
 *
 * @return \Closure
 */
function apply(...$fns)
{
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
         * function it may cancel executing subsequent apply functions
         */
        $cancelApply = false;
        return array_reduce($fns, function ($d, callable $f) use ($data, &$cancelApply) {
            return $cancelApply ? $d : $f($d, $cancelApply);
        }, $data);
    };
}
