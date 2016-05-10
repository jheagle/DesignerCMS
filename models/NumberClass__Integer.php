<?php

// header("Content-type: application/json");
if (!class_exists('DataType')) {
    $currentFile = basename(__FILE__, '.php');
    exit("Core 'DataType' Undefined. '{$currentFile}' must not be called directly.");
}

// $value = 128;
// $testValue = 32;
// $integer = new Int($value, false);
// var_dump($integer);
// var_dump($integer->getValue());
// var_dump($testValue);
// var_dump($integer->isEven());
// var_dump($integer->getAbsolute());
// var_dump($integer->isEqual('4294967295'));
// echo 'Bitwise Add: '.$integer->add($testValue)."\n";
// echo 'True Add: '.($integer->getValue() + $testValue)."\n";
// echo 'Bitwise Subtract: '.$integer->subtract($testValue)."\n";
// echo 'True Subtract: '.($integer->getValue() - $testValue)."\n";
// echo 'Bitwise Mulitiply: '.$integer->multiplyBy($testValue)."\n";
// echo 'True Multiple: '.($integer->getValue() * $testValue)."\n";
// echo 'Bitwise Divide: '.$integer->divideBy($testValue)."\n";
// echo 'True Divide: '.($integer->getValue() / $testValue)."\n";
// echo 'Bitwise Modulo: '.$integer->modulo($testValue)."\n";
// echo 'True Modulo: '.($integer->getValue() % $testValue)."\n";

class Int extends Number
{
    protected $min;
    protected $max;
    protected $bits = 32;
    protected static $length;

    public function __construct($value = 0, $length = 0, $isSigned = true)
    {
        parent::__construct($value, $length, $isSigned);
        self::setMin();
        self::setMax();
        self::setlength($length);
        self::setValue($this->value);
    }

    protected function setMin()
    {
        $this->min = (int) ($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : 0);
    }

    protected function setMax()
    {
        if ($this->bits >= $this->systemMaxBits && !$this->isSigned) {
            $this->max = (int) ((1 << $this->systemMaxBits - 1) - 1);
        } else {
            $this->max = (int) ($this->isSigned ? (1 << $this->bits - 1) - 1 : (1 << $this->bits) - 1);
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
        } elseif ($length > strlen((string) $this->max)) {
            $length = (int) strlen((string) $this->max);
        }
        $this->length = $length;
    }

    public function getValue()
    {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value)
    {
        if ($value > $this->min && (int) ((float) $value) < $this->min && $this->bits >= $this->systemMaxBits && !$this->isSigned) {
            return $this->value = (int) ((float) $value);
        }

        if ($value < $this->min) {
            $value = (int) $this->min;
        } elseif ($value > $this->max) {
            $value = (int) $this->max;
        }

        return $this->value = (int) $value;
    }
}

class BigInt extends Number
{
    protected $min;
    protected $max;
    protected $bits = 64;
    protected static $length;
    protected static $absoluteMax;

    public function __construct($value = 0, $length = 0, $isSigned = true)
    {
        parent::__construct($value, $isSigned);
        self::setMin();
        self::setMax();
        $absoluteMax = $this->bits > $this->systemMaxBits ? '9223372036854775807' : $this->max;
        $this->absoluteMax = $this->isSigned ? $absoluteMax : '18446744073709551616';
        self::setlength($length);
        self::setValue($this->value);
    }

    protected function setMin()
    {
        $this->min = (int) ($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : 0);
    }

    protected function setMax()
    {
        $this->max = (int) ($this->bits > $this->systemMaxBits || !$this->isSigned ? ((1 << $this->systemMaxBits - 1) - 1) : (1 << $this->bits - 1) - 1);
    }

    public function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > strlen((string) $this->absoluteMax)) {
            $length = (int) strlen((string) $this->absoluteMax);
        }
        $this->length = $length;
    }

    public function getValue()
    {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value)
    {
        if ($this->bits > $this->systemMaxBits && ($value > $this->max || $value < $this->min)) {
            if ($value > $this->min && !$this->isSigned && (int) ((float) $value) < $this->min && $value <= ($this->max + (-1 ^ ~$this->max) + 1)) {
                return $this->value = (int) ((float) $value);
            }

            if ($value < 99999999999999 && $value > -99999999999999) {
                return $this->value = (float) $value;
            }
            $charLength = strlen($this->absoluteMax);
            $part = (int) ($charLength / 2);
            $first = substr($this->absoluteMax, 0, $part);

            $valLength = strlen((string) $value);
            $start = $valLength - $part;
            $valFirst = substr((string) $value, 0, $start);

            if ($valFirst > $first) {
                $value = $this->absoluteMax;
            }

            return $this->value = (string) $value;
        }

        if ($value < $this->min) {
            $value = (int) $this->min;
        } elseif ($value > $this->max) {
            $value = (int) $this->max;
        }

        return $this->value = (int) $value;
    }
}

class MediumInt extends Int
{
    protected $bits = 24;

    public function __construct($value = 0, $length = 0, $isSigned = true)
    {
        parent::__construct($value, $length, $isSigned);
    }
}

class SmallInt extends Int
{
    protected $bits = 16;

    public function __construct($value = 0, $length = 0, $isSigned = true)
    {
        parent::__construct($value, $length, $isSigned);
    }
}

class TinyInt extends Int
{
    protected $bits = 8;

    public function __construct($value = 0, $length = 0, $isSigned = true)
    {
        parent::__construct($value, $length, $isSigned);
    }
}
