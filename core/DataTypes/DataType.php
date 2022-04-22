<?php

namespace Core\DataTypes;

use Core\DataTypes\Interfaces\DataTypeObject;
use Core\Traits\Declarative;
use Core\Traits\LazyAssignment;

/**
 * Class DataType
 *
 * @package Core\DataTypes
 */
abstract class DataType implements DataTypeObject
{
    use Declarative;
    use LazyAssignment;

    public const PRIMITIVE_ARRAY = 'array';
    public const PRIMITIVE_BOOLEAN = 'boolean';
    public const PRIMITIVE_CALLABLE = 'callable';
    public const PRIMITIVE_FLOAT = 'float';
    public const PRIMITIVE_INTEGER = 'integer';
    public const PRIMITIVE_ITERABLE = 'iterable';
    public const PRIMITIVE_NULL = 'null';
    public const PRIMITIVE_OBJECT = 'object';
    public const PRIMITIVE_RESOURCE = 'resource';
    public const PRIMITIVE_STRING = 'string';

    public const PRIMITIVES = [
        self::PRIMITIVE_BOOLEAN,
        self::PRIMITIVE_FLOAT,
        self::PRIMITIVE_INTEGER,
        self::PRIMITIVE_STRING,
        self::PRIMITIVE_ARRAY,
        self::PRIMITIVE_CALLABLE,
        self::PRIMITIVE_ITERABLE,
        self::PRIMITIVE_OBJECT,
        self::PRIMITIVE_NULL,
        self::PRIMITIVE_RESOURCE,
    ];

    public const SCALARS = [
        self::PRIMITIVE_BOOLEAN,
        self::PRIMITIVE_FLOAT,
        self::PRIMITIVE_INTEGER,
        self::PRIMITIVE_STRING,
    ];

    public const COMPOUNDS = [
        self::PRIMITIVE_ARRAY,
        self::PRIMITIVE_CALLABLE,
        self::PRIMITIVE_ITERABLE,
        self::PRIMITIVE_OBJECT,
    ];

    public const SPECIALS = [
        self::PRIMITIVE_NULL,
        self::PRIMITIVE_RESOURCE,
    ];

    protected mixed $value;
    protected string $primitiveType = 'object';
    protected static int $systemMaxBits;

    /**
     * Retrieve the value stored in this data type.
     *
     * @return mixed
     */
    abstract public function getValue(): mixed;

    /**
     * Assign a value to this data type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function setValue(mixed $value): mixed;

    /**
     * DataType constructor.
     *
     * @param $value
     * @param array $settings
     */
    public function __construct($value = null, array $settings = [])
    {
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        self::applyMemberSettings($settings);
    }

    /**
     * Get the PHP data type representation that closely matches the value of this data type.
     *
     * @return string
     */
    public function getPrimitiveType(): string
    {
        return $this->primitiveType;
    }

    /**
     * Get the maximum number of bits usable for a block of storage.
     *
     * @return int
     */
    public function getSystemMaxBits(): int
    {
        return self::$systemMaxBits;
    }

    /**
     * Compare the value of this data type to some incoming value or data for equality.
     *
     * @param mixed|DataType $dataType
     *
     * @return bool
     */
    public function isEqual(mixed $dataType): bool
    {
        return is_a($dataType, DataType::class)
            ? $this->getValue() === $dataType->getValue()
            : $this->getValue() === $dataType;
    }

    /**
     * Produce a string to describe this data type.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getClassDescription();
    }
}
