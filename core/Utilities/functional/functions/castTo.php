<?php

use Core\DataTypes\GenericType;

if (!function_exists('castTo')) {
    /**
     * Return a value with the applied type-cast.
     *
     * @param mixed $value
     * @param string $castType
     *
     * @return string|int|bool|array|object|float|null
     */
    function castTo(mixed $value, string $castType): string|int|bool|array|null|object|float
    {
        return match ($castType) {
            'array' => (array)$value,
            'bool', 'boolean' => (bool)$value,
            'callable' => is_callable($value) ? $value : fn() => $value,
            'double', 'float', 'real' => (float)$value,
            'int', 'integer' => (int)$value,
            'object' => (object)$value,
            'string' => (string)$value,
            'unset' => null,
            default => GenericType::applyCast($value, $castType, true),
        };
    }
}

$castTo = static function (mixed $value, string $castType): string|array|bool|int|null|object|float {
    return castTo($value, $castType);
};

if (($declareGlobal ?? false) && ($GLOBALS['castTo'] ?? false)) {
    $GLOBALS['castTo'] = $castTo;
}
