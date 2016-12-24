<?php

$currentFile = basename(__FILE__, '.php');
if (!class_exists('DataType')) {
    exit("Core 'DataType' Undefined. '{$currentFile}' must not be called directly.");
}
foreach (array_keys($CORE) as $filename) {
    if (strstr($filename, "{$currentFile}_")) {
        require_once $CORE[$filename];
        continue;
    }
}

class String_DT extends DataType {

    protected $primitiveType = 'string';
    protected static $charSet;

    /**
     * 
     * @param type $value
     * @param type $settings
     */
    public function __construct($value, $settings = []) {
        parent::__construct($value, $settings);
        $settings = array_merge([
            'charSet' => 'UTF-8',
            ], $settings);
        self::$charSet = $settings['charSet'];
        self::setValue($this->value);
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = mb_convert_encoding($value, self::$charSet);
    }

}

class VarChar_DT extends String_DT {

    protected $min;
    protected $max;
    protected $bits = 16;
    protected $length;

    public function __construct($value, $settings = []) {
        parent::__construct($value, $settings);
        $settings = array_merge([
            'length' => null,
            'charSet' => 'UTF-8',
            ], $settings);
        self::setMin();
        self::setMax();
        if ($settings['length'] === null) {
            $settings['length'] = $this->max;
        }
        self::setLength($settings['length']);
        self::setValue($this->value);
    }

    protected function setMin() {
        $this->min = 0;
    }

    protected function setMax() {
        if ($this->bits >= self::$systemMaxBits) {
            $this->max = (int) ((1 << self::$systemMaxBits - 1) - 1);
        } else {
            $this->max = (int) ((1 << $this->bits) - 1);
        }
    }

    public function getLength() {
        return $this->length;
    }

    protected function setLength($length) {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > $this->max) {
            $length = (int) $this->max;
        }
        $this->length = $length;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $value = substr($value, 0, $this->length);
        $this->value = $value;
    }

}

class Char_DT extends VarChar_DT {

    protected $bits = 8;

    public function __construct($value, $settings = []) {
        parent::__construct($value, $settings);
        $settings = array_merge([
            'length' => null,
            'charSet' => 'UTF-8',
            ], $settings);
        self::setValue($this->value);
    }

    public function getValue() {
        return rtrim($this->value);
    }

    public function setValue($value) {
        $this->value = str_pad($value, $this->length, ' ', STR_PAD_RIGHT);
    }

}
