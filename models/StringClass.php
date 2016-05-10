<?php

// header('Content-type: application/json');
$currentFile = basename(__FILE__, '.php');
if (!class_exists('DataType')) {
    exit("Core 'DataType' Undefined. '{$currentFile}' must not be called directly.");
}
foreach (array_keys($MODELS) as $filename) {
    if (strstr($filename, "{$currentFile}_")) {
        require_once $MODELS[$filename];
        continue;
    }
}

// $value = 'Hello';
// $datatype = new VarChar($value, 100);
// var_dump($datatype);
// var_dump($datatype->getValue());
class String extends DataType
{
    protected static $charSet;
    public function __construct($value, $charSet = 'UTF-8')
    {
        parent::__construct($value);
        $this->charSet = $charSet;
        self::setValue($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = mb_convert_encoding($value, $this->charSet);
    }
}

class VarChar extends String
{
    protected $min;
    protected $max;
    protected $bits = 16;
    protected  static $length;
    public function __construct($value, $length = null, $charSet = 'UTF-8')
    {
        parent::__construct($value, $charSet);
        self::setMin();
        self::setMax();
        if ($length === null) {
            $length = $this->max;
        }
        self::setlength($length);
        self::setValue($this->value);
    }

    protected function setMin()
    {
        $this->min = 0;
    }

    protected function setMax()
    {
        if ($this->bits >= $this->systemMaxBits) {
            $this->max = (int) ((1 << $this->systemMaxBits - 1) - 1);
        } else {
            $this->max = (int) ((1 << $this->bits) - 1);
        }
    }

    public function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > $this->max) {
            $length = (int) $this->max;
        }
        $this->length = $length;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $value = substr($value, 0, $this->length);
        $this->value = $value;
    }
}

class Char extends VarChar
{
    protected $bits = 8;
    public function __construct($value, $length = null, $charSet = 'UTF-8')
    {
        parent::__construct($value, $length, $charSet);
        self::setValue($this->value);
    }

    public function getValue()
    {
        return rtrim($this->value);
    }

    public function setValue($value)
    {
        $this->value = str_pad($value, $this->length, ' ', STR_PAD_RIGHT);
    }
}
