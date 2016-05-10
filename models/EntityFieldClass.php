<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/global_include.php';
require_once $MODELS['DataTypeClass'];

// $field = new Field('column', 'BigInt', 0, 50, Field::ZERO_FILL | Field::UNSIGNED);
// $field->setValue('999999999999999999');
// var_dump($field);
// var_dump($field->getValue());

class Field
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

    protected static $name;
    protected $dataType;
    protected static $attributes;
    protected static $default;

    public function __construct($name = '', $dataType = 'String', $default = '', $length = null, $attributes = self::NOT_NULL)
    {
        $this->name = strtolower(str_replace(' ', '_', $name));
        $dataTypeClassName = ucwords(strtolower($dataType));
        $dataTypeClass = property_exists($dataTypeClassName, 'length') ? new $dataTypeClassName($default, $length) : new $dataTypeClassName($default);
        $this->attributes = $attributes & self::PRIMARY_KEY || $attributes & self::UNIQUE ? $attributes | self::REQUIRED : $attributes;
        if ($this->hasAttr(self::UNSIGNED) && $dataTypeClass instanceof Number) {
            $dataTypeClass = property_exists($dataTypeClassName, 'length') ? new $dataTypeClassName($default, $length, false) : new $dataTypeClassName($default, false);
        }
        $this->dataType = $dataTypeClass;
        $this->default = $this->dataType->getValue();
    }

    public function getValue()
    {
        $value = $this->dataType->getValue();
        if ($this->dataType instanceof Number && property_exists(get_class($this->dataType), 'length') && $this->hasAttr(self::ZERO_FILL)) {
            $value = str_pad($value, $this->dataType->getLength(), '0', STR_PAD_LEFT);
        }

        return  $value;
    }

    public function setValue($value)
    {
        return $this->dataType->setValue($value);
    }

    public function hasAttr($attr)
    {
        return ($this->attributes & $attr) === $attr;
    }

    public function hasAttribute($attr)
    {
        return self::hasAttr($attr);
    }
}
