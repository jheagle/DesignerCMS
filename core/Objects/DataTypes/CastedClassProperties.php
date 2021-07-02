<?php

namespace Core\Objects\DataTypes;

use Core\Objects\DataTransferCollection;
use ReflectionProperty;

/**
 * Class CastedClassProperties stores an array of CastedClassProperty instances.
 *
 * @package Core\Objects\DataTypes
 */
class CastedClassProperties extends DataTransferCollection
{
    public static function fromArray(array $properties = []): DataTransferCollection
    {
        return new self(
            array_map(
                fn(ReflectionProperty|array $property) => is_a($property, ReflectionProperty::class)
                    ? CastedClassProperty::fromClassProperty($property)
                    : CastedClassProperty::fromArray($property),
                $properties
            )
        );
    }
}