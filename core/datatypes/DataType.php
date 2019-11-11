<?php

namespace Core\DataTypes;

use Core\Utilities\Traits\Declarative;
use Core\Utilities\Traits\LazyAssignment;

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

    /** @var mixed $value */
    protected $value;

    /** @var string $primitiveType */
    protected $primitiveType = 'object';

    /** @var int $systemMaxBits */
    protected static $systemMaxBits;

    /**
     * @return mixed
     */
    abstract public function getValue();

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function setValue($value);

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
     * @return string
     */
    public function getPrimitiveType(): string
    {
        return $this->primitiveType;
    }

    /**
     * @return int
     */
    public function getSystemMaxBits(): int
    {
        return self::$systemMaxBits;
    }

    /**
     * @param mixed|DataType $datatype
     *
     * @return bool
     */
    public function isEqual($datatype): bool
    {
        return is_a($datatype, DataType::class)
            ? $this->getValue() === $datatype->getValue()
            : $this->getValue() === $datatype;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function __toString(): string
    {
        return $this->getClassDescription();
    }
}
