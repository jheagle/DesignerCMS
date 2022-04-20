<?php
/**
 * Given a min, if a given number is less than min, then bound the number to the min value.
 *
 * @param float|int $min
 * @param float|int $number
 *
 * @return int|float
 */
$minBound = static function (float|int $min, float|int $number) {
    return $number <= $min ? $min : $number;
};

if (($declareGlobal ?? false) && !function_exists('minBound')) {
    $GLOBALS['minBound'] = $minBound;
    function minBound($min = 0, $number = 0)
    {
        return $GLOBALS['minBound']($min, $number);
    }
}
