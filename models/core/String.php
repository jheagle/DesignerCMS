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

    protected static $charSet;

    public function __construct($value, $charSet = 'UTF-8') {
        parent::__construct($value);
        self::$charSet = $charSet;
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
    protected static $length;

    public function __construct($value, $length = null, $charSet = 'UTF-8') {
        parent::__construct($value, $charSet);
        self::setMin();
        self::setMax();
        if ($length === null) {
            $length = $this->max;
        }
        self::setlength($length);
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
        return self::$length;
    }

    protected function setLength($length) {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > $this->max) {
            $length = (int) $this->max;
        }
        self::$length = $length;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $value = substr($value, 0, self::$length);
        $this->value = $value;
    }

}

class Char_DT extends VarChar_DT {

    protected $bits = 8;

    public function __construct($value, $length = null, $charSet = 'UTF-8') {
        parent::__construct($value, $length, $charSet);
        self::setValue($this->value);
    }

    public function getValue() {
        return rtrim($this->value);
    }

    public function setValue($value) {
        $this->value = str_pad($value, $this->length, ' ', STR_PAD_RIGHT);
    }

}
