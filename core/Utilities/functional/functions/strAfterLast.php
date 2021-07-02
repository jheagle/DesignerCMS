<?php

/**
 * Retrieve the part of the string after the last occurrence of a substring.
 *
 * @param string $subject
 * @param string $search
 *
 * @return string
 */
$strAfterLast = static function (string $subject, string $search = ' '): string {
    return substr($subject, strrpos($subject, $search) + strlen($search));
};

if (($declareGlobal ?? false) && !function_exists('strAfterLast')) {
    $GLOBALS['strAfterLast'] = $strAfterLast;
    function strAfterLast(string $subject, string $search = ' ')
    {
        return $GLOBALS['strAfterLast']($subject, $search);
    }
}