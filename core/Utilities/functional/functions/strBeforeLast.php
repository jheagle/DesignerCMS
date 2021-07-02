<?php

/**
 * Retrieve the part of the string before the last occurrence of a substring.
 *
 * @param string $subject
 * @param string $search
 *
 * @return string
 */
$strBeforeLast = static function (string $subject, string $search = ' '): string {
    return substr($subject, 0, strrpos($subject, $search));
};

if (($declareGlobal ?? false) && !function_exists('strBeforeLast')) {
    $GLOBALS['strBeforeLast'] = $strBeforeLast;
    function strBeforeLast(string $subject, string $search = ' ')
    {
        return $GLOBALS['strBeforeLast']($subject, $search);
    }
}