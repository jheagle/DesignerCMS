<?php
/**
 * Given a min, if a given number is less than min, then bound the number to the min value.
 *
 * @param int|float $min
 * @param int|float $number
 *
 * @return int|float
 */
$minBound = static function ($min = 0, $number) {
    return $number <= $min ? $min : $number;
};

if ($declareGlobal ?? false && !function_exists('minBound')) {
    $GLOBALS['minBound'] = $minBound;
    function minBound($min = 0, $number = 0)
    {
        return $GLOBALS['minBound']($min, $number);
    }
}
