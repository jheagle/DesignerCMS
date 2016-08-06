<?php

if (!isset($ROOT)) {
    $ROOT = dirname(__DIR__);
}
require_once $ROOT . '/global_include.php';
require_once $MODELS['DataTypeClass'];

class Field implements Potential {

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

    public function __construct($name = '', $dataType = 'String', $default = '', $length = null, $attributes = self::NOT_NULL) {
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

    public function getValue() {
        $value = $this->dataType->getValue();
        if ($this->dataType instanceof Number && property_exists(get_class($this->dataType), 'length') && $this->hasAttr(self::ZERO_FILL)) {
            $value = str_pad($value, $this->dataType->getLength(), '0', STR_PAD_LEFT);
        }

        return $value;
    }

    public function setValue($value) {
        return $this->dataType->setValue($value);
    }

    public function hasAttr($attr) {
        return ($this->attributes & $attr) === $attr;
    }

    public function hasAttribute($attr) {
        return self::hasAttr($attr);
    }

    public function __toString() {
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
