<?php
/**
 * Given a max, if a given number is greater than max, then bound the number to the max value.
 *
 * @param int|float $max
 * @param int|float $number
 *
 * @return int|float
 */
$maxBound = static function ($max = 0, $number) {
    return $number >= $max ? $max : $number;
};

if ($declareGlobal ?? false && !function_exists('maxBound')) {
    $GLOBALS['maxBound'] = $maxBound;
    function maxBound($max = 0, $number = 0)
    {
        return $GLOBALS['maxBound']($max, $number);
    }
}
