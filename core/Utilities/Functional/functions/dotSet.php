<?php

if (!function_exists('dotSet')) {
    /**
     * Given and array of object with a dot-notation string, then return the value.
     *
     * @param array|object $arrayObject
     * @param string $dotNotation
     * @param mixed $value
     *
     * @return array|object
     */
    function dotSet(array|object &$arrayObject, string $dotNotation, mixed $value): array|object
    {
        $isArray = is_array($arrayObject);
        $key = strBefore($dotNotation, '.');
        $lastKey = !$key;
        if ($lastKey) {
            $key = $dotNotation;
        }
        if ($key === '*') {
            foreach ($arrayObject as &$wildValue) {
                if ($lastKey) {
                    $wildValue = $value;
                    continue;
                }
                if (!is_array($wildValue) && !is_object($wildValue)) {
                    continue;
                }
                dotSet($wildValue, strAfter($dotNotation, '.'), $value);
            }
            return $arrayObject;
        }
        if ($lastKey) {
            if ($isArray) {
                $arrayObject[$dotNotation] = $value;
            } else {
                $arrayObject->$dotNotation = $value;
            }
            return $arrayObject;
        }
        $next  = $isArray ? $arrayObject[$key] ?? [] : $arrayObject->$key ?? [];
        $returnValue = dotSet(
            $next,
            strAfter($dotNotation, '.'),
            $value
        );
        if ($isArray) {
            $arrayObject[$key] = $returnValue;
        } else {
            $arrayObject->$key = $returnValue;
        }
        return $arrayObject;
    }
}

$dotSet = static function (array|object $arrayObject, string $dotNotation, $default = null): mixed {
    return dotSet($arrayObject, $dotNotation, $default);
};

if (($declareGlobal ?? false) && ($GLOBALS['dotSet'] ?? false)) {
    $GLOBALS['dotSet'] = $dotSet;
}
