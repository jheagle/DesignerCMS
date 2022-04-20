<?php

/**
 * Provide a default that will be used if a given value is null.
 *
 * @param $default
 * @param $value
 *
 * @return mixed|null
 */
$defaultValue = static function ($default, $value) {
    return coalesce(
        function ($value): bool {
            return !!$value;
        },
        $value,
        $default
    );
};

if (($declareGlobal ?? false) && !function_exists('defaultValue')) {
    $GLOBALS['defaultValue'] = $defaultValue;
    function defaultValue($default, $value)
    {
        return $GLOBALS['defaultValue']($default, $value);
    }
}
