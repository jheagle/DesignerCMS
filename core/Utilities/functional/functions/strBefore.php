<?php
if (!function_exists('strBefore')) {
    /**
     * Retrieve the part of the string before the first occurrence of a substring.
     *
     * @param string $subject
     * @param string $search
     *
     * @return string
     */
    function strBefore(string $subject, string $search = ' '): string
    {
        return substr($subject, 0, strpos($subject, $search));
    }
}

$strBefore = static function (string $subject, string $search = ' '): string {
    return strBefore($subject, $search);
};

if (($declareGlobal ?? false) && ($GLOBALS['strBefore'] ?? false)) {
    $GLOBALS['strBefore'] = $strBefore;
}