<?php

if (!function_exists('dotNotate')) {
    /**
     * Convert an array or object to a single dimensional associative array with dot notation.
     *
     * @param array|object $arrayObject
     * @param string $prepend
     *
     * @return array
     */
    function dotNotate(array|object $arrayObject, string $prepend = ''): array
    {
        $results = [];

        foreach ($arrayObject as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $results = array_merge($results, dotNotate($value, $prepend . $key . '.'));
                continue;
            }
            $results[$prepend . $key] = $value;
        }

        return $results;
    }
}

$dotNotate = static function (array|object $arrayObject, string $prepend = ''): array {
    return dotNotate($arrayObject, $prepend);
};

if (($declareGlobal ?? false) && ($GLOBALS['dotNotate'] ?? false)) {
    $GLOBALS['dotNotate'] = $dotNotate;
}
