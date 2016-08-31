<?php

if (!class_exists('DataType')) {
    $currentFile = basename(__FILE__, '.php');
    exit("Core 'DataType' Undefined. '{$currentFile}' must not be called directly.");
}

class Int_DT extends Number_DT {

    protected $min;
    protected $max;
    protected $bits = 32;
    protected $length;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value, $length, $isSigned);
        self::setMin();
        self::setMax();
        self::setlength($length);
        self::setValue($this->value);
    }

    protected function setMin() {
        $this->min = (int) ($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : 0);
    }

    protected function setMax() {
        if ($this->bits >= self::$systemMaxBits && !$this->isSigned) {
            $this->max = (int) ((1 << self::$systemMaxBits - 1) - 1);
        } else {
            $this->max = (int) ($this->isSigned ? (1 << $this->bits - 1) - 1 : (1 << $this->bits) - 1);
        }
    }

    public function getLength() {
        return $this->length;
    }

    protected function setLength($length) {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > strlen((string) $this->max)) {
            $length = (int) strlen((string) $this->max);
        }
        $this->length = $length;
    }

    public function getValue() {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value) {
        if ($value > $this->min && (int) ((float) $value) < $this->min && $this->bits >= self::$systemMaxBits && !$this->isSigned) {
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

class BigInt_DT extends Number_DT {

    protected $min;
    protected $max;
    protected $bits = 64;
    protected $length;
    protected $absoluteMax;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
        self::setMin();
        self::setMax();
        $absoluteMax = $this->bits > self::$systemMaxBits ? '9223372036854775807' : $this->max;
        $this->absoluteMax = $this->isSigned ? $absoluteMax : '18446744073709551616';
        self::setlength($length);
        self::setValue($this->value);
    }

    protected function setMin() {
        if ($this->isSigned) {
            $this->min = (int) ($this->bits > self::$systemMaxBits ? (~0) ^ (1 << $this->bits - 1) - 1 : ((1 << self::$systemMaxBits - 1) - 1));
        }
        else {
            $this->min = 0;
        }
    }

    protected function setMax() {
        if ($this->bits > self::$systemMaxBits) {
            $this->max = (int) ($this->isSigned ? (1 << $this->bits - 1) - 1 : ((1 << self::$systemMaxBits - 1) - 1));
        }
        else {
            $this->max = (int) ((~0) ^ (1 << $this->bits - 1) - 1);
        }
    }

    public function getLength() {
        return $this->length;
    }

    protected function setLength($length) {
        if ($length < $this->min) {
            $length = (int) $this->min;
        } elseif ($length > strlen((string) $this->absoluteMax)) {
            $length = (int) strlen((string) $this->absoluteMax);
        }
        $this->length = $length;
    }

    public function getValue() {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value) {
        if (($this->bits > self::$systemMaxBits || !$this->isSigned) && ($value > $this->max || $value < $this->min)) {

            if ($value < 99999999999999 && $value > -99999999999999) {
                if ($value > $this->min && !$this->isSigned && (int) ((float) $value) < $this->min && $value <= ($this->max + (-1 ^ ~$this->max) + 1)) {
                    return $this->value = (int) ((float) $value);
                }
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

class MediumInt_DT extends Int_DT {

    protected $bits = 24;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value, $length, $isSigned);
    }

}

class SmallInt_DT extends Int_DT {

    protected $bits = 16;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value, $length, $isSigned);
    }

}

class TinyInt_DT extends Int_DT {

    protected $bits = 8;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value, $length, $isSigned);
    }

}
