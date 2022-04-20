<?php

if (!function_exists('strAfter')) {
    /**
     * Retrieve the part of the string after the first occurrence of a substring.
     *
     * @param string $subject
     * @param string $search
     *
     * @return string
     */
    function strAfter(string $subject, string $search = ' '): string
    {
        return substr($subject, strpos($subject, $search) + strlen($search));
    }
}

$strAfter = static function (string $subject, string $search = ' '): string {
    return strAfter($subject, $search);
};

if (($declareGlobal ?? false) && ($GLOBALS['strAfter'] ?? false)) {
    $GLOBALS['strAfter'] = $strAfter;
}