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

    const PRIMITIVE_ARRAY = 'array';
    const PRIMITIVE_BOOLEAN = 'boolean';
    const PRIMITIVE_CALLABLE = 'callable';
    const PRIMITIVE_FLOAT = 'float';
    const PRIMITIVE_INTEGER = 'integer';
    const PRIMITIVE_ITERABLE = 'iterable';
    const PRIMITIVE_NULL = 'null';
    const PRIMITIVE_OBJECT = 'object';
    const PRIMITIVE_RESOURCE = 'resource';
    const PRIMITIVE_STRING = 'string';

    const PRIMITIVES = [
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

    const SCALARS = [
        self::PRIMITIVE_BOOLEAN,
        self::PRIMITIVE_FLOAT,
        self::PRIMITIVE_INTEGER,
        self::PRIMITIVE_STRING,
    ];

    const COMPOUNDS = [
        self::PRIMITIVE_ARRAY,
        self::PRIMITIVE_CALLABLE,
        self::PRIMITIVE_ITERABLE,
        self::PRIMITIVE_OBJECT,
    ];

    const SPECIALS = [
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
     * DataType constructor.
     *
     * @param $value
     * @param array $settings
     */
    public function __construct($value = null, array $settings = [])
    {
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        self::applyMemberSettings($settings);
        $this->setValue($value);
    }

    /**
     * @return int
     */
    public function getSystemMaxBits(): int
    {
        return self::$systemMaxBits;
    }

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
     * @return string
     */
    public function getPrimitiveType(): string
    {
        return $this->primitiveType;
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
