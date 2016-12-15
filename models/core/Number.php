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

class Number_DT extends String_DT {

    protected $length;
    protected $isSigned;
    protected $valueSplit;

    public function __construct($value = 0, $length = 0, $isSigned = true) {
        parent::__construct($value);
        $this->isSigned = $isSigned;
        $this->filter = $this->isSigned ? '/[^-0-9.]/' : '/[^0-9.]/';
        self::setValue($this->value);
        self::setLength($length);
    }

    public function getLength() {
        return $this->length;
    }

    protected function setLength($length) {
        $this->length = $length;
    }

    public function getPaddedValue() {
        return str_pad($this->value, $this->getLength(), '0', STR_PAD_LEFT);
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->valueSplit = [];
        $valueFiltered = preg_replace($this->filter, '', $value);
        $deciParts = explode('.', (string) $valueFiltered);
        $maxLength = strlen((string) PHP_INT_MAX) - 1;
        if (count($deciParts) > 1) {
            $deciSplit = str_split(array_pop($deciParts), $maxLength);
            $intNum = implode('', $deciParts);
            $valLength = strlen($intNum);
            for ($i = $valLength; $i > 0; $i -= $maxLength) {
                $charCnt = $i > $maxLength ? $maxLength : $i;
                $this->valueSplit[] = substr($intNum, $i - $charCnt, $charCnt);
            }
            $revCnt = 1 - count($deciSplit);
            array_walk($deciSplit, function($value, $key) use(&$revCnt) {
                $this->valueSplit[$revCnt--] = $value;
            });
        } else {
            $this->valueSplit = str_split(implode('', $deciParts), $maxLength);
        }

        $this->value = count($this->valueSplit) < 2 ? (float) $valueFiltered : $valueFiltered;
    }

    public function getSigned() {
        return $this->isSigned;
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
        if (is_a($number, 'Number_DT')) {
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
            $m <<= 1;
        }

        return $z;
    }

    protected function internalDivide($x, $y) {
        $c = 0;
        $sign = 0;

        if ($x < 0) {
            $x = $this->negate($x);
            $sign ^= 1;
        }

        if ($y < 0) {
            $y = $this->negate($y);
            $sign ^= 1;
        }

        if ($y != 0) {
            while ($x >= $y) {
                $x = $this->internalSubtract($x, $y);
                ++$c;
            }
        }
        if ($sign) {
            $c = $this->negate($c);
        }

        return $c;
    }

    public function add($number) {
        if (is_a($number, 'Number_DT')) {
            $number = $number->getValue();
        }

        return $this->internalAdd($this->getValue(), $number);
    }

    public function subtract($number) {
        if (is_a($number, 'Number_DT')) {
            $number = $number->getValue();
        }

        return $this->internalSubtract($this->getValue(), $number);
    }

    public function multiplyBy($number) {
        if (is_a($number, 'Number_DT')) {
            $number = $number->getValue();
        }

        return $this->internalMultiply($this->getValue(), $number);
    }

    public function divideBy($number) {
        if (is_a($number, 'Number_DT')) {
            $number = $number->getValue();
        }

        return $this->modulo($number) ? $this->getValue() / $number : $this->internalDivide($this->getValue(), $number);
    }

    //TODO: use this function logic with perfroming math on numbers stored in string (ex: BigInt)
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
        if (is_a($number, 'Number_DT')) {
            return $this->value === $number->getValue();
        }
        if ($this->value === $number) {
            return true;
        }
        if ($this->getValue() === $number) {
            return true;
        }

        $isSigned = preg_match('/^-/', "{$number}") ? true : false;

        $dataType = new self($number, $isSigned);
        switch (gettype($number)) {
            case 'integer':
                $dataType = new BigInt_DT($number, $isSigned);
                break;
            case 'double':
                $dataType = new Double_DT($number, $isSigned);
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
        $availBits = self::$systemMaxBits - 1;

        return ($value ^ ($value >> $availBits)) - ($value >> $availBits);
    }

    public function negate($number) {
        return $this->internalAdd(~$number, 1);
    }

}
