<?php

/**
 * Retrieve the env setting if it exists.
 *
 * @param string $name
 * @param mixed|null $default
 * @return mixed
 */
$envGet = static function (string $name, mixed $default = null): mixed {
    return dotGet($_ENV, $name, $default);
};

if (($declareGlobal ?? false) && !function_exists('envGet')) {
    $GLOBALS['envGet'] = $envGet;
    function envGet($name, $default = null)
    {
        return $GLOBALS['envGet']($name, $default);
    }
}
