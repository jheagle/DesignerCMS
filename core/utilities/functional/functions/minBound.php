<?php

/**
 * @param int $min
 * @param $number
 *
 * @return int
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
