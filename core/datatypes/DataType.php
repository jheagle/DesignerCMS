<?php

namespace Core\DataTypes;

abstract class DataType implements DataTypeObject
{

    protected $value;

    protected $primitiveType = 'object';

    protected static $systemMaxBits;

    public function __construct($value, $settings = [])
    {
        $settings = array_merge([], $settings);
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        return $this->value = $value;
    }

    /**
     * @param mixed|DataType $datatype
     *
     * @return bool
     */
    public function isEqual($datatype): bool
    {
        if (is_a($datatype, 'DataType')) {
            return $this->getValue() === $datatype->getValue();
        }

        return $this->getValue() === $datatype;
    }

    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            if (is_array($v) || is_object($v)) {
                $string .= "{$k}: " . count((array)$v);
                continue;
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}
