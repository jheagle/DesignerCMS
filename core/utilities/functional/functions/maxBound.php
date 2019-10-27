<?php

/**
 * @param int $max
 * @param $number
 *
 * @return int
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
