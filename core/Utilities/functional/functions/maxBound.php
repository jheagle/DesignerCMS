<?php

/**
 * Given a max, if a given number is greater than max, then bound the number to the max value.
 *
 * @param float|int $max
 * @param float|int $number
 *
 * @return int|float
 */
$maxBound = static function (float|int $max, float|int $number) {
    return $number >= $max ? $max : $number;
};

if (($declareGlobal ?? false) && !function_exists('maxBound')) {
    $GLOBALS['maxBound'] = $maxBound;
    function maxBound($max = 0, $number = 0)
    {
        return $GLOBALS['maxBound']($max, $number);
    }
}
