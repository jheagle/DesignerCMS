<?php

namespace Core\Entity;

use Core\DataTypes\DataType;
use Core\DataTypes\Interfaces\Potential;
use Core\DataTypes\Numbers\NumberDt;
use Core\DataTypes\Strings\StringDt;

/**
 * Class Field
 *
 * @package Core\Entity
 */
class Field implements Potential
{

    public const PRIMARY_KEY = 1;

    public const NOT_NULL = 2;

    public const UNIQUE = 4;

    public const BINARY = 8;

    public const UNSIGNED = 16;

    public const ZERO_FILL = 32;

    public const AUTO_INCREMENT = 64;

    public const REQUIRED = 128;

    public const INDEX = 256;

    public const FULLTEXT = 512;

    /** @var string $name */
    private string $name;

    /** @var string|DataType|null $dataType */
    private string|DataType|null $dataType = null;

    /** @var int $attributes */
    private int $attributes;

    /** @var mixed $default */
    private mixed $default;

    /**
     * The column type exactly as the database states it (e.g. 'int unsigned', "enum('a','b')"), when known.
     */
    private ?string $sqlType;

    /**
     * False when the database type has no DataType class yet (DESI-4): the value is then held as a StringDt, and
     * $sqlType says what the column really is.
     */
    private bool $typeSupported;

    /**
     * Field constructor.
     *
     * @param string $name
     * @param string|DataType|null $dataType
     * @param mixed $default
     * @param int|null $length
     * @param int $attributes
     */
    public function __construct(
        string $name = '',
        string|DataType|null $dataType = StringDt::class,
        mixed $default = '',
        ?int $length = null,
        int $attributes = self::NOT_NULL,
        ?string $sqlType = null,
        bool $typeSupported = true
    ) {
        $this->sqlType = $sqlType;
        $this->typeSupported = $typeSupported;
        $this->name = strtolower(str_replace(' ', '_', $name));
        // Set Required attribute if this field is Unique or a Primary Key
        $this->attributes = $attributes & self::PRIMARY_KEY || $attributes & self::UNIQUE
            ? $attributes | self::REQUIRED
            : $attributes;
        if ($this->hasAttr(self::ZERO_FILL)) {
            // Only Unsigned numbers can have Zero Fill attribute
            $this->attributes |= self::UNSIGNED;
        }
        // TODO: Implement a way of applying DataType-specific settings
        // Only a known length is passed: a null one would replace the DataType's own default (and the number types
        // only accept an int), so a number field without a length couldn't be made at all
        $settings = ['isSigned' => !$this->hasAttr(self::UNSIGNED)];
        if ($length !== null) {
            $settings['length'] = $length;
        }
        // A null default (a nullable column defaulting to NULL, or no default at all) is kept as null; the DataType then
        // starts from its own empty value, as the DataTypes don't hold null
        $this->dataType = $default === null ? new $dataType(settings: $settings) : new $dataType($default, $settings);
        $this->default = $default === null ? null : $this->dataType->getValue();
    }

    /**
     * The column name (lower case, spaces as underscores).
     *
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    final public function getDataType(): DataType
    {
        return $this->dataType;
    }

    final public function getDefault(): mixed
    {
        return $this->default;
    }

    final public function getAttributes(): int
    {
        return $this->attributes;
    }

    /**
     * The column type exactly as the database states it, or null when the Field wasn't read from a database.
     */
    final public function getSqlType(): ?string
    {
        return $this->sqlType;
    }

    /**
     * Whether the column type has a DataType of its own (false: held as a StringDt until DESI-4 adds the type).
     */
    final public function isTypeSupported(): bool
    {
        return $this->typeSupported;
    }

    /**
     * @return int|mixed|string
     */
    final public function getValue(): mixed
    {
        if ($this->dataType instanceof NumberDt && property_exists(
                get_class($this->dataType),
                'length'
            ) && $this->hasAttr(self::ZERO_FILL)) {
            return $this->dataType->getPaddedValue();
        }

        return $this->dataType->getValue();
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    final public function setValue(mixed $value): mixed
    {
        return $this->dataType->setValue($value);
    }

    /**
     * @param mixed $attr
     *
     * @return bool
     */
    final public function hasAttr(mixed $attr): bool
    {
        return ($this->attributes & $attr) === $attr;
    }

    /**
     * @param mixed $attr
     *
     * @return bool
     */
    final public function hasAttribute(mixed $attr): bool
    {
        return self::hasAttr($attr);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}
