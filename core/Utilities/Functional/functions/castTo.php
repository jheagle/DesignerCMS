<?php

use Core\Contracts\Keyable;
use Core\DataTypes\GenericType;

if (!function_exists('castTo')) {
    /**
     * Return a value with the applied type-cast.
     *
     * @param mixed $value
     * @param ReflectionType|string $castType
     *
     * @return string|int|bool|array|object|float|null
     */
    function castTo(mixed $value, ReflectionType|string $castType): string|int|bool|array|null|object|float
    {
        $typeName = $castType;
        if ($castType instanceof ReflectionNamedType) {
            $typeName = $castType->getName();
        }
        if ($castType instanceof ReflectionUnionType || $castType instanceof ReflectionIntersectionType) {
            // Union types have multiple possible types
            // Figure out which of the types fits best
            $unionTypes = $castType->getTypes();
            $builtInTypes = [];
            foreach ($unionTypes as $unionType) {
                if ($unionType->isBuiltin()) {
                    // Built ins are standard PHP data types
                    $builtInTypes[] = $unionType->getName();
                    continue;
                }
                $className = $unionType->getName();
                if ($value instanceof $className) {
                    // If we have a matching Class type, use that
                    return castTo($value, $unionType);
                }
            }
            // Get the best match of the standard types
            $originType = gettype($value);
            $matchedType = array_search($originType, $builtInTypes);
            if ($matchedType !== false) {
                return castTo($value, $matchedType);
            }
            // Otherwise, return as-is
            return $value;
        }
        if (is_a($castType, Keyable::class) && (is_string($value) || is_int($value))) {
            return $castType::fromKey($value);
        }
        return match ($typeName) {
            'array' => (array)$value,
            'bool', 'boolean' => (bool)$value,
            'callable' => is_callable($value) ? $value : fn() => $value,
            'double', 'float', 'real' => (float)$value,
            'int', 'integer' => (int)$value,
            'object' => (object)$value,
            'string' => (string)$value,
            'unset' => null,
            default => GenericType::applyCast($value, $typeName, true),
        };
    }
}

$castTo = static function (mixed $value, string $castType): string|array|bool|int|null|object|float {
    return castTo($value, $castType);
};

if (($declareGlobal ?? false) && ($GLOBALS['castTo'] ?? false)) {
    $GLOBALS['castTo'] = $castTo;
}
