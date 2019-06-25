<?php

namespace Core\DataTypes\Numbers;

use Core\DataTypes\Strings\StringDt;

/**
 * Class NumberDt
 *
 * @package Core\DataTypes
 */
class NumberDt extends StringDt
{
    protected $length;

    protected $isSigned;

    protected $isNegative;

    protected $valueSplit;

    protected $filter = '/[^\d.]/';

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'length' => 0,
                'isSigned' => true,
                'primitiveType' => 'float'
            ],
            $settings
        ));
        self::setValue($this->value);
        self::setLength($this->length);
    }

    public function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        $this->length = $length;
    }

    public function getPaddedValue()
    {
        return str_pad(
            $this->getPreservedValue(),
            $this->getLength(),
            '0',
            STR_PAD_LEFT
        );
    }

    protected function getPreservedValue()
    {
        if (array_key_exists(-1, $this->valueSplit)) {
            $tempSplitValue = $this->valueSplit;
            $tempSplitValue[-1] = ".{$this->valueSplit[-1]}";

            return implode('', $tempSplitValue);
        }

        return implode('', $this->valueSplit);
    }

    protected function setPreservedValue($value)
    {
        $this->valueSplit = [];
        $valueFiltered = preg_replace(
            $this->filter,
            '',
            $value
        ); // Apply the numeric filter to the incoming value
        $deciParts = explode(
            '.',
            (string)$valueFiltered
        ); // Split on decimal points
        $maxLength = strlen(
                (string)PHP_INT_MAX
            ) - 1; // capture the max int length - 1
        // We wish to store numbers less than the length of max int in order to provide sufficient memory
        // space for performing arithmetic.
        if (count($deciParts) > 1) {
            $deciSplit = str_split(
                array_pop($deciParts),
                $maxLength
            ); // Choose the final post-decimal value
            // as the decimal portion and split based on max int

            $intNum = implode(
                '',
                $deciParts
            ); // merge the remaining values to preserve as pre-decimal value
            // we will seperate the pre-decimal value based on max int length - 1
            // custom logic is used in order to store the fields so that the least significant part starts at 0 index
            for ($i = strlen($intNum); $i > 0; $i -= $maxLength) {
                $charCnt = $i > $maxLength ? $maxLength : $i;
                $this->valueSplit[] = (int)substr($intNum, $i - $charCnt,
                    $charCnt);
            }

            // Add the elements of decimal fields to the main split value array
            // These fields will be indexed with negative values to show that they
            // come after 0 (the 0 index being least significant integer value)
            array_walk(
                $deciSplit,
                function ($value, $key) {
                    $this->valueSplit[~$key] = (int)$value;
                }
            );
        } else {
            $intNum = implode(
                '',
                $deciParts
            ); // merge the remaining values to preserve as pre-decimal value
            // we will seperate the pre-decimal value based on max int length - 1
            // custom logic is used in order to store the fields so that the least significant part starts at 0 index
            for ($i = strlen($intNum); $i > 0; $i -= $maxLength) {
                $charCnt = $i > $maxLength ? $maxLength : $i;
                $this->valueSplit[] = (int)substr($intNum, $i - $charCnt,
                    $charCnt);
            }
        }
        krsort(
            $this->valueSplit
        ); // we sort the indexes from highest to lowest (so negative values come last)

        return $this->valueSplit;
    }

    public function getValue()
    {
        if ($this->isNegative) {
            return is_string($this->value) ? '-' . $this->value : $this->negate(
                $this->value
            );
        }

        return $this->value;
    }

    public function setValue($value)
    {
        $this->isNegative = strstr($value, '-') && $this->isSigned;
        $result = $this->setPreservedValue($value);

        return $this->value = count($this->valueSplit) < 2 && !array_key_exists(
            -1,
            $this->valueSplit
        ) ? (int)$this->getPreservedValue() : $this->getPreservedValue();
    }

    public function getSigned()
    {
        return $this->isSigned;
    }

    public function isEven()
    {
        return !($this->value & 1);
    }

    public function isPowerOfTwo($number)
    {
        return $number && !($number & ($number - 1));
    }

    public function isMersenne($number)
    {
        return $number && !(($number + 1) & $number);
    }

    public function logPowerOfTwo($number)
    {
        $exponent = 0;
        while ($number >>= 1) {
            ++$exponent;
        }

        return $exponent;
    }

    public function exponent($number)
    {

    }

    public function modulo($number)
    {
        if (is_a($number, 'NumberDt')) {
            return $number->getValue() & ($number->getValue() - 1) || ($number->getValue() + 1) & $number->getValue() ? $this->getValue() % $number->getValue() : $this->getValue() & ($number->getValue() - 1);
        }

        return $this->isPowerOfTwo($number) || $this->isMersenne(
            $number
        ) ? $this->getValue() % $number : $this->getValue() & ($number - 1);
    }

    protected function internalAdd($x, $y)
    {
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

    protected function internalSubtract($x, $y)
    {
        return $this->internalAdd($x, $this->negate($y));
    }

    protected function internalMultiply($x, $y)
    {
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

    protected function internalDivide($x, $y)
    {
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

    public function add($number)
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->internalAdd($this->getValue(), $number);
    }

    public function subtract($number)
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->internalSubtract($this->getValue(), $number);
    }

    public function multiplyBy($number)
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->internalMultiply($this->getValue(), $number);
    }

    public function divideBy($number)
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->modulo($number) ? $this->getValue() / $number : $this->internalDivide($this->getValue(),
            $number);
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

    /**
     * Return the absolute value of the number being the distance from zero
     *
     * @return int
     */
    public function getAbsolute()
    {
        $value = $this->getValue();
        $availBits = self::$systemMaxBits - 1;

        return ($value ^ ($value >> $availBits)) - ($value >> $availBits);
    }

    /**
     * Get the inverse of this number (positive -> negative / negative ->
     * positive)
     *
     * @param int $number
     *
     * @return int
     */
    public function negate($number)
    {
        return $this->internalAdd(~$number, 1);
    }

}
