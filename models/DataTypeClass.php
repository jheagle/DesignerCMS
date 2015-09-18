<?php

//header("Content-type: application/json");

abstract class DataType {

    protected $value;
    protected static $systemMaxBits;

    public function __construct($value) {
        $this->systemMaxBits = PHP_INT_SIZE << 3;
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        return $this->value = $value;
    }

}

class Number extends DataType {

    protected static $isSigned;
    protected static $filter;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value);
        $this->isSigned = $isSigned;
        $this->filter = $this->isSigned ? '/[^-0-9.]/' : '/[^0-9.]/';
        self::setValue($this->value);
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $this->systemMaxBits === 64 ? (float) preg_replace($this->filter, '', $value) : preg_replace($this->filter, '', $value);
    }

    public function isEven() {
        return !($this->value & 1);
    }

    public function isPowerOfTwo($number) {
        return $number && !($number & ($number - 1));
    }

    public function isMersenne($number) {
        return $number && !(($number + 1) & $number);
    }

    public function logPowerOfTwo($number) {
        $exponent = 0;
        while ($number >>= 1) {
            ++$exponent;
        }
        return $exponent;
    }

    public function exponent($number) {
        
    }

    public function modulo($number) {
        if (is_a($number, 'Number')) {
            return $number->getValue() & ($number->getValue() - 1) || ($number->getValue() + 1) & $number->getValue() ? $this->getValue() % $number->getValue() : $this->getValue() & ($number->getValue() - 1);
        }
        return $this->isPowerOfTwo($number) || $this->isMersenne($number) ? $this->getValue() % $number : $this->getValue() & ($number - 1);
    }

    protected function internalAdd($x, $y) {
        $a = 0;
        $b = 0;
        do {
            $a = $x & $y;
            $b = $x ^ $y;
            $x = $a << 1;
            $y = $b;
        } while ($a);
        return $b;
    }

    protected function internalSubtract($x, $y) {
        return $this->internalAdd($x, $this->negate($y));
    }

    protected function internalMultiply($x, $y) {
        $m = 1;
        $z = 0;
        if ($x < 0) {
            $x = $this->negate($x);
            $y = $this->negate($y);
        }

        while ($x >= $m && $y) {
            if ($x & $m) {
                $z = $this->internalAdd($y, $z);
            }
            $y <<= 1;
            $m<<= 1;
        }
        return $z;
    }

    protected function internalDivide($x, $y) {
        $c = 0;
        $sign = 0;

        if ($x < 0) {
            $x = $this->negate($x);
            $sign^=1;
        }

        if ($y < 0) {
            $y = $this->negate($y);
            $sign^=1;
        }

        if ($y != 0) {
            while ($x >= $y) {
                $x = $this->internalSubtract($x, $y);
                ++$c;
            }
        }
        if ($sign) {
            $c = $$this->negate($c);
        }
        return $c;
    }

    public function add($number) {
        if (is_a($number, 'Number')) {
            $number = $number->getValue();
        }
        return $this->internalAdd($this->getValue(), $number);
    }

    public function subtract($number) {
        if (is_a($number, 'Number')) {
            $number = $number->getValue();
        }
        return $this->internalSubtract($this->getValue(), $number);
    }

    public function multiplyBy($number) {
        if (is_a($number, 'Number')) {
            $number = $number->getValue();
        }
        return $this->internalMultiply($this->getValue(), $number);
    }

    public function divideBy($number) {
        if (is_a($number, 'Number')) {
            $number = $number->getValue();
        }
        return $this->modulo($number) ? $this->getValue() / $number : $this->internalDivide($this->getValue(), $number);
    }

//    public function add($number) {
//        $maxLength = strlen('' . (PHP_INT_MAX / 10) . '');
//        if (is_a($number, 'Number')) {
//            $number = $number->getValue();
//        }
//        $carry = $this->getValue() & $number;
//        $result = $this->getValue() ^ $number;
//        while ($carry != 0) {
//            $shiftedcarry = $carry << 1;
//            $carry = $result & $shiftedcarry;
//            $result ^= $shiftedcarry;
//        }
//        return $result;
//    }

    public function isEqual($number) {
        if (is_a($number, 'Number')) {
            return $this->value === $number->getValue();
        }
        if ($this->value === $number) {
            return true;
        }
        if ($this->getValue() === $number) {
            return true;
        }

        $isSigned = preg_match('/^-/', "{$number}") ? true : false;

        $dataType = new Number($number, $isSigned);
        switch (gettype($number)) {
            case 'integer':
                $dataType = new BigInt($number, $isSigned);
                break;
            case 'double':
                $dataType = new Double($number, $isSigned);
                break;
            case 'string':
                break;
            default:
                return false;
        }
        return $this->getValue() === $dataType->getValue();
    }

    public function getAbsolute() {
        $value = $this->getValue();
        $availBits = $this->systemMaxBits - 1;
        return ($value ^ ($value >> $availBits)) - ($value >> $availBits);
    }

    public function negate($number) {
        return $this->internalAdd(~$number, 1);
    }

}

//$value = 128;
//$testValue = 32;
//$integer = new Int($value, false);
//var_dump($integer);
//var_dump($integer->getValue());
//var_dump($testValue);
//var_dump($integer->isEven());
//var_dump($integer->getAbsolute());
//var_dump($integer->isEqual('4294967295'));
//echo "Bitwise Add: " . $integer->add($testValue) . "\n";
//echo "True Add: " . ($integer->getValue() + $testValue) . "\n";
//echo "Bitwise Subtract: " . $integer->subtract($testValue) . "\n";
//echo "True Subtract: " . ($integer->getValue() - $testValue) . "\n";
//echo "Bitwise Mulitiply: " . $integer->multiplyBy($testValue) . "\n";
//echo "True Multiple: " . ($integer->getValue() * $testValue) . "\n";
//echo "Bitwise Divide: " . $integer->divideBy($testValue) . "\n";
//echo "True Divide: " . ($integer->getValue() / $testValue) . "\n";
//echo "Bitwise Modulo: " . $integer->modulo($testValue) . "\n";
//echo "True Modulo: " . ($integer->getValue() % $testValue) . "\n";

class Int extends Number {

    protected $min;
    protected $max;
    protected $bits = 32;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
        self::setMin();
        self::setMax();
        self::setValue($this->value);
    }

    protected function setMin() {
        $this->min = (int) ($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : 0);
    }

    protected function setMax() {
        if ($this->bits >= $this->systemMaxBits && !$this->isSigned) {
            $this->max = (int) ((1 << $this->systemMaxBits - 1) - 1);
        } else {
            $this->max = (int) ($this->isSigned ? (1 << $this->bits - 1) - 1 : (1 << $this->bits) - 1);
        }
    }

    public function getValue() {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value) {
        if ($value > $this->min && (int) ((float) $value) < $this->min && $this->bits >= $this->systemMaxBits && !$this->isSigned) {
            return $this->value = (int) ((float) $value);
        }

        if ($value < $this->min) {
            $this->value = (int) $this->min;
        } elseif ($value > $this->max) {
            $this->value = (int) $this->max;
        }

        return $this->value = (int) $value;
    }

}

class BigInt extends Number {

    protected $bits = 64;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
        self::setMin();
        self::setMax();
        self::setValue($this->value);
    }

    protected function setMin() {
        $this->min = (int) ($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : 0);
    }

    protected function setMax() {
        $this->max = (int) ($this->bits > $this->systemMaxBits || !$this->isSigned ? ((1 << $this->systemMaxBits - 1) - 1) : (1 << $this->bits - 1) - 1);
    }

    public function getValue() {
        return $this->value < $this->min ? $this->max + ($this->value ^ ~$this->max) + 1 : $this->value;
    }

    public function setValue($value) {
        if ($this->bits > $this->systemMaxBits && ($value > $this->max || $value < $this->min)) {
            if ($value > $this->min && !$this->isSigned && (int) ((float) $value) < $this->min && $value <= ($this->max + (-1 ^ ~$this->max) + 1)) {
                return $this->value = (int) ((float) $value);
            }

            if ($value < 99999999999999 && $value > -99999999999999) {
                return $this->value = (float) $value;
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

class MediumInt extends Int {

    protected $bits = 24;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
    }

}

class SmallInt extends Int {

    protected $bits = 16;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
    }

}

class TinyInt extends Int {

    protected $bits = 8;

    public function __construct($value = 0, $isSigned = true) {
        parent::__construct($value, $isSigned);
    }

}

abstract class Field {
    
}
