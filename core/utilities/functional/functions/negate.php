<?php
/**
 * Reverse the signed bit for a number by reversing all of the bits then adding one to push the signed bit to the
 * opposite state.
 * Note, this fails with all bits toggled on since reversing and adding one will overflow back into a negative number.
 *
 * @param int $number
 *
 * @return int
 */
$negate = static function (int $number): int {
    return add(~$number, 1);
};

if ($declareGlobal ?? false && !function_exists('negate')) {
    $GLOBALS['negate'] = $negate;
}