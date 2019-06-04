<?php

namespace DesignerCms\Models\Core;

// Add add subtypes at bottom

interface DataTypeObject extends Potential
{

    public function getValue();

    public function setValue($value);
}

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

    public function isEqual($datatype)
    {
        if (is_a($datatype, 'DataType')) {
            return $this->getValue() === $number->getValue();
        }

        return $this->getValue() === $datatype;
    }

    public function __toString()
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
