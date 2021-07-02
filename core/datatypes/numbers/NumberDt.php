<?php

namespace Core\DataTypes\Numbers;

use Core\DataTypes\Strings\StringDt;
use Core\Utilities\Functional\Pure;

/**
 * Class NumberDt
 *
 * @package Core\DataTypes
 */
class NumberDt extends StringDt
{
    protected ?string $filter = '/[^\d.]/';
    protected ?bool $isNegative;
    protected ?bool $isSigned;
    protected ?int $length;
    protected ?array $valueSplit;

    /**
     * NumberDt constructor.
     *
     * @param int $value
     * @param array $settings
     */
    public function __construct($value = 0, array $settings = [])
    {
        parent::__construct(
            $value,
            array_merge(
                [
                    'length' => 0,
                    'isSigned' => true,
                    'primitiveType' => 'float',
                ],
                $settings
            )
        );
        self::setValue($this->value);
        self::setLength($this->length);
    }

    /**
     * Increase the value of this NumberDt with the provided number
     *
     * @param float|int|NumberDt $number
     *
     * @return int
     */
    public function add(float|NumberDt|int $number): int
    {
        if (is_a($number, NumberDt::class)) {
            $number = $number->getValue();
        }

        return Pure::add($this->getValue(), $number);
    }

    public function divideBy(int|float|NumberDt $number): float|int
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->modulo($number) ? $this->getValue() / $number : $this->internalDivide(
            $this->getValue(),
            $number
        );
    }

    public function exponent($number)
    {
    }

    /**
     * Return the absolute value of the number being the distance from zero
     *
     * @return int
     */
    public function getAbsolute(): int
    {
        $value = $this->getValue();
        $availBits = self::$systemMaxBits - 1;

        return ($value ^ ($value >> $availBits)) - ($value >> $availBits);
    }

    /**
     * Return the filter used to devise a number from a string
     *
     * @return string|null
     */
    public function getFilter(): ?string
    {
        return $this->filter;
    }

    /**
     * Check if this number has the signed bit and it is set
     *
     * @return bool|null
     */
    public function getIsNegative(): ?bool
    {
        return $this->isNegative;
    }

    /**
     * Check if this number has a signed bit
     *
     * @return bool|null
     */
    public function getIsSigned(): ?bool
    {
        return $this->isSigned;
    }

    /**
     * Return the length of this number
     *
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getPaddedValue(): string
    {
        return str_pad(
            $this->getPreservedValue(),
            $this->getLength(),
            '0',
            STR_PAD_LEFT
        );
    }

    public function getSigned(): ?bool
    {
        return $this->isSigned;
    }

    public function getValue(): mixed
    {
        if ($this->isNegative) {
            return is_string($this->value) ? '-' . $this->value : $this->negate(
                $this->value
            );
        }

        return $this->value;
    }

    /**
     * Check if this value was split to preserve the true value
     *
     * @return array|null
     */
    public function getValueSplit(): ?array
    {
        return $this->valueSplit;
    }

    public function isEven(): bool
    {
        return !($this->value & 1);
    }

    public function isMersenne($number): bool
    {
        return $number && !(($number + 1) & $number);
    }

    public function isPowerOfTwo($number): bool
    {
        return $number && !($number & ($number - 1));
    }

    public function logPowerOfTwo($number): int
    {
        $exponent = 0;
        while ($number >>= 1) {
            ++$exponent;
        }

        return $exponent;
    }

    public function modulo(int|float|NumberDt $number): int
    {
        if (is_a($number, 'NumberDt')) {
            return $number->getValue() & ($number->getValue() - 1) || ($number->getValue() + 1) & $number->getValue(
            ) ? $this->getValue() % $number->getValue() : $this->getValue() & ($number->getValue() - 1);
        }

        return $this->isPowerOfTwo($number) || $this->isMersenne(
            $number
        ) ? $this->getValue() % $number : $this->getValue() & ($number - 1);
    }

    public function multiplyBy(int|float|NumberDt $number): int
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->internalMultiply($this->getValue(), $number);
    }

    /**
     * Get the inverse of this number (positive -> negative / negative ->
     * positive)
     *
     * @param int $number
     *
     * @return int
     */
    public function negate(int $number): int
    {
        return Pure::negate($number);
    }

    public function setValue(mixed $value): string|int
    {
        $this->isNegative = strstr($value, '-') && $this->isSigned;
        $this->setPreservedValue($value);

        return $this->value = count($this->valueSplit) < 2 && !array_key_exists(
            -1,
            $this->valueSplit
        ) ? (int)$this->getPreservedValue() : $this->getPreservedValue();
    }

    public function subtract(int|float|NumberDt $number): int
    {
        if (is_a($number, 'NumberDt')) {
            $number = $number->getValue();
        }

        return $this->internalSubtract($this->getValue(), $number);
    }

    /**
     * @return string
     */
    protected function getPreservedValue(): string
    {
        if (array_key_exists(-1, $this->valueSplit)) {
            $tempSplitValue = $this->valueSplit;
            $tempSplitValue[-1] = ".{$this->valueSplit[-1]}";

            return implode('', $tempSplitValue);
        }

        return implode('', $this->valueSplit);
    }

    protected function internalDivide($x, $y): int
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

    protected function internalMultiply($x, $y): int
    {
        $m = 1;
        $z = 0;
        if ($x < 0) {
            $x = $this->negate($x);
            $y = $this->negate($y);
        }

        while ($x >= $m && $y) {
            if ($x & $m) {
                $z = Pure::add($y, $z);
            }
            $y <<= 1;
            $m <<= 1;
        }

        return $z;
    }

    protected function internalSubtract($x, $y): int
    {
        return Pure::add($x, $this->negate($y));
    }

    /**
     * Apply a fixed length
     *
     * @param int $length
     *
     * @return int
     */
    protected function setLength(int $length): int
    {
        $this->length = $length;
        return $this->length;
    }

    /**
     * @param $value
     *
     * @return array|null
     */
    protected function setPreservedValue($value): ?array
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
            // we will separate the pre-decimal value based on max int length - 1
            // custom logic is used in order to store the fields so that the least significant part starts at 0 index
            for ($i = strlen($intNum); $i > 0; $i -= $maxLength) {
                $charCnt = $i > $maxLength ? $maxLength : $i;
                $this->valueSplit[] = (int)substr(
                    $intNum,
                    $i - $charCnt,
                    $charCnt
                );
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
            // we will separate the pre-decimal value based on max int length - 1
            // custom logic is used in order to store the fields so that the least significant part starts at 0 index
            for ($i = strlen($intNum); $i > 0; $i -= $maxLength) {
                $charCnt = $i > $maxLength ? $maxLength : $i;
                $this->valueSplit[] = (int)substr(
                    $intNum,
                    $i - $charCnt,
                    $charCnt
                );
            }
        }
        krsort(
            $this->valueSplit
        ); // we sort the indexes from highest to lowest (so negative values come last)

        return $this->valueSplit;
    }
    //TODO: use this function logic with performing math on numbers stored in string (ex: BigInt)
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
}
