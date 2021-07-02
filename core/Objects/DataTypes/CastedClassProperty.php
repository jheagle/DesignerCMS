<?php

namespace Core\Objects\DataTypes;

use Core\Objects\DataTransferObject;
use Core\Utilities\Functional\Pure;
use ReflectionProperty;

/**
 * Class CastedClassProperty stores details of a class property.
 *
 * @package Core\Objects\DataTypes
 */
class CastedClassProperty extends DataTransferObject
{
    public const SCOPE_PUBLIC = 'public';
    public const SCOPE_PROTECTED = 'protected';
    public const SCOPE_PRIVATE = 'private';

    public const SCOPES = [
        self::SCOPE_PUBLIC,
        self::SCOPE_PROTECTED,
        self::SCOPE_PRIVATE,
    ];

    public mixed $default = null;
    public bool $isStatic = false;
    public string $name;
    public mixed $value = null;
    public string $scope;

    /**
     * Create an instance of this class using ReflectionProperty.
     *
     * @param ReflectionProperty $property
     *
     * @return static
     */
    public static function fromClassProperty(ReflectionProperty $property): self
    {
        $scope = static::getReflectionScope($property);
        $property = [
            'default' => $property->getDefaultValue(),
            'isStatic' => $property->isStatic(),
            'name' => Pure::dotGet($property, 'name'),
            'value' => $scope === self::SCOPE_PUBLIC ? $property->getValue() : null,
            'scope' => $scope,
        ];
        return new self($property);
    }

    /**
     * Retrieve the applicable scope for this property.
     *
     * @param ReflectionProperty $property
     *
     * @return string
     */
    private static function getReflectionScope(ReflectionProperty $property): string
    {
        if ($property->isPrivate()) {
            return self::SCOPE_PRIVATE;
        }
        if ($property->isProtected()) {
            return self::SCOPE_PROTECTED;
        }
        return self::SCOPE_PUBLIC;
    }
}