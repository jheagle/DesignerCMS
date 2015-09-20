<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');
require_once($MODELS['DataTypeClass']);

class Field {

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

    public function __construct($name, $dataType, $default = '', $attributes = self::NOT_NULL) {
        $this->name = strtolower(str_replace(' ', '_', $name));
        $dataTypeClassName = ucwords(strtolower($dataType));
        $dataTypeClass = new $dataTypeClassName($default);
        $this->attributes = $attributes & self::PRIMARY_KEY || $attributes & self::UNIQUE ? $attributes | self::REQUIRED : $attributes;
        $this->dataType = hasAttribute(self::UNSIGNED) && $dataTypeClass instanceof Number ? new $dataTypeClassName($default, false) : $dataTypeClass;
        $this->default = $this->dataType->getValue();
    }

    public function getValue() {
        return $this->datatype->getValue();
    }

    public function setValue($value) {
        return $this->datatype->setValue($value);
    }

    public function hasAttr($attr) {
        return (($this->attributes & $attr) == $attr);
    }

    public function hasAttribute($attr) {
        return self::hasAttr($attr);
    }

}
