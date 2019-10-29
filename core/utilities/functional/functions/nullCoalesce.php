<?php
/**
 * Provide a default that will be used if a given value is null.
 *
 * @param mixed|null $default
 * @param mixed|null $value
 *
 * @return mixed|null
 */
$nullCoalesce = static function ($default = null, $value) {
    return $value ?? $default;
};

if ($declareGlobal ?? false && !function_exists('nullCoalesce')) {
    $GLOBALS['nullCoalesce'] = $nullCoalesce;
    function nullCoalesce($default = null)
    {
        return $GLOBALS['nullCoalesce']($default);
    }
}
