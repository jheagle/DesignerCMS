<?php

namespace Core\Objects\DataTypes;

use Core\Objects\DataTransferObject;
use Core\Utilities\Functional\Pure;

/**
 * Class CastedClassType stores the name of a casted class along with its property details.
 *
 * @package Core\Objects\DataTypes
 */
class CastedClassType extends DataTransferObject
{
    public string $className;
    public ?CastedClassProperties $classProperties;

    public static function fromArray(array $properties): static
    {
        $properties['classProperties'] = CastedClassProperties::fromArray(Pure::dotGet($properties, 'classProperties'));
        return new self($properties);
    }
}