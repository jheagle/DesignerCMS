<?php

if (!function_exists('add')) {
    /**
     * Given two values, combine these values and return the total.
     *
     * @param int $x
     * @param int $y
     *
     * @return int
     */
    function add(int $x, int $y): int
    {
        do {
            $a = $x & $y;
            $b = $x ^ $y;
            $x = $a << 1;
            $y = $b;
        } while ($a);
        return $b;
    }
}

$add = static function (int $x, int $y): int {
    return add($x, $y);
};

if (($declareGlobal ?? false) && ($GLOBALS['add'] ?? false)) {
    $GLOBALS['add'] = $add;
}
