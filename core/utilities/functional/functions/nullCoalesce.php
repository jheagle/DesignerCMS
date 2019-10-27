<?php

/**
 * @param null $default
 * @param $value
 *
 * @return null
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
