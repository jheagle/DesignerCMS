<?php

if (!function_exists('dotSet')) {
    /**
     * Given and array of object with a dot-notation string, then return the value.
     *
     * @param array|object $arrayObject
     * @param string $dotNotation
     * @param mixed $value
     *
     * @return mixed
     */
    function dotSet(array|object $arrayObject, string $dotNotation, mixed $value): mixed
    {
        $isArray = is_array($arrayObject);
        $key = strBefore($dotNotation, '.');
        if (!$key) {
            if ($isArray) {
                $arrayObject[$dotNotation] = $value;
            } else {
                $arrayObject->$dotNotation = $value;
            }
            return $arrayObject;
        }
        $returnValue = dotSet(
            $isArray ? $arrayObject[$key] ?? [] : $arrayObject->$key ?? [],
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
