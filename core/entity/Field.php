<?php

namespace Core\Entity;

use Core\DataTypes\DataType;
use Core\DataTypes\Numbers\NumberDt;
use Core\DataTypes\Potential;

/**
 * Class Field
 *
 * @package Core\Entity
 */
class Field implements Potential
{

    const PRIMARY_KEY = 1;

    const NOT_NULL = 2;

    const UNIQUE = 4;

    const BINARY = 8;

    const UNSIGNED = 16;

    const ZERO_FILL = 32;

    const AUTO_INCREMENT = 64;

    const REQUIRED = 128;

    const INDEX = 256;

    const FULLTEXT = 512;

    /** @var string $name */
    protected $name;

    /** @var null|DataType $dataType */
    protected $dataType = null;

    /** @var int $attributes */
    protected $attributes;

    /** @var mixed $default */
    protected $default;

    /**
     * Field constructor.
     *
     * @param string $name
     * @param string $dataType
     * @param string $default
     * @param null $length
     * @param int $attributes
     */
    public function __construct(
        $name = '',
        $dataType = 'String',
        $default = '',
        $length = null,
        $attributes = self::NOT_NULL
    ) {
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
        $dataTypeClassName = '\Core\DataTypes\Numbers\\' . $dataType . 'Dt';
        $this->dataType = new $dataTypeClassName(
            $default,
            ['length' => $length, 'isSigned' => !$this->hasAttr(self::UNSIGNED)]
        );
        $this->default = $this->dataType->getValue();
    }

    /**
     * @return int|mixed|string
     */
    public function getValue()
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
     * @param $value
     *
     * @return mixed
     */
    public function setValue($value)
    {
        return $this->dataType->setValue($value);
    }

    /**
     * @param $attr
     *
     * @return bool
     */
    public function hasAttr($attr)
    {
        return ($this->attributes & $attr) === $attr;
    }

    /**
     * @param $attr
     *
     * @return bool
     */
    public function hasAttribute($attr)
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
