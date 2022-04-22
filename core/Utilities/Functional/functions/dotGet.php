<?php

if (!function_exists('dotGet')) {
    /**
     * Given and array of object with a dot-notation string, then return the value.
     *
     * @param array|object $arrayObject
     * @param string $dotNotation
     * @param mixed $default
     *
     * @return mixed
     */
    function dotGet(array|object $arrayObject, string $dotNotation, mixed $default = null): mixed
    {
        $isArray = is_array($arrayObject);
        $key = strBefore($dotNotation, '.');
        if (!$key) {
            return $isArray ? $arrayObject[$dotNotation] ?? $default : $arrayObject->$dotNotation ?? $default;
        }
        if ($key === '*') {
            $result = [];
            foreach ($arrayObject as $wildKey => $wildValue) {
                $result[$wildKey] = dotGet($wildValue, strAfter($dotNotation, '.'), $default);
            }
            return $result;
        }
        if ($isArray && !array_key_exists($key, $arrayObject)) {
            return $default;
        }
        if (!$isArray && !property_exists($arrayObject, $key)) {
            return $default;
        }
        $next = $isArray ? $arrayObject[$key] : $arrayObject->$key;
        if (!is_array($next) && !is_object($next)) {
            return $default;
        }
        return dotGet($next, strAfter($dotNotation, '.'), $default);
    }
}

$dotGet = static function (array|object $arrayObject, string $dotNotation, mixed $default = null): mixed {
    return dotGet($arrayObject, $dotNotation, $default);
};

if (($declareGlobal ?? false) && ($GLOBALS['dotGet'] ?? false)) {
    $GLOBALS['dotGet'] = $dotGet;
}
