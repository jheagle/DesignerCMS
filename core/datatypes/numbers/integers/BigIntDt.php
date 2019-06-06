<?php

namespace Core\DataTypes\Numbers;

class BigIntDt extends NumberDt
{

    protected $primitiveType = 'int';

    protected $min;

    protected $max;

    protected $bits = 64;

    protected $absoluteMax;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'length' => 0,
                'isSigned' => true,
            ],
            $settings
        ));
        self::setMin();
        self::setMax();
        self::setLength($settings['length']);
        self::setValue($this->value);
    }

    protected function setMin()
    {
        if ($this->isSigned) {
            $this->min = (int)($this->bits > self::$systemMaxBits ? (~0) ^ (1 << $this->bits - 1) - 1 : ((1 << self::$systemMaxBits - 1) - 1));
        } else {
            $this->min = 0;
        }
    }

    protected function setMax()
    {
        if ($this->bits >= self::$systemMaxBits) {
            $this->max = (int)($this->isSigned ? (~0) ^ (1 << $this->bits - 1) - 1 : (~0) ^ (1 << $this->bits - 1) - 1);
        } else {
            $this->max = (int)($this->isSigned ? PHP_INT_MAX : (1 << $this->bits) - 1);
        }
        $absoluteMax = $this->bits > self::$systemMaxBits ? '9223372036854775807' : $this->max;
        $this->absoluteMax = $this->isSigned ? $absoluteMax : '18446744073709551616';
    }

    protected function setLength($length)
    {
        if ($length < 0) {
            $length = 0;
        } elseif ($length > strlen((string)$this->absoluteMax)) {
            $length = (int)strlen((string)$this->absoluteMax);
        }
        $this->length = $length;
    }

    public function getValue()
    {
        return parent::getValue();
    }

    public function setValue($value)
    {
        $this->isNegative = strstr($value, '-') && $this->isSigned;
        $this->valueSplit = array_filter(
            parent::setPreservedValue($value),
            function ($key) {
                return $key >= 0;
            },
            ARRAY_FILTER_USE_KEY
        );
        $value = count($this->valueSplit) < 2 && !array_key_exists(
            -1,
            $this->valueSplit
        ) ? (int)$this->getPreservedValue() : $this->getPreservedValue();
        if (($this->bits > self::$systemMaxBits || !$this->isSigned) && ($value > $this->max || $value < $this->min)) {

            if ($value < 99999999999999 && $value > -99999999999999) {
                if ($value > $this->min && !$this->isSigned && (int)((float)$value) < $this->min && $value <= ($this->max + (-1 ^ ~$this->max) + 1)) {
                    return $this->value = (int)((float)$value);
                }

                return $this->value = (float)$value;
            }
        }

        if ($value < $this->min) {
            $value = (int)$this->min;
        } elseif ($value > $this->max && $this->bits <= self::$systemMaxBits) {
            $value = (int)$this->max;
        } else {
            $charLength = strlen($this->absoluteMax);
            $part = (int)($charLength / 2);
            $first = substr($this->absoluteMax, 0, $part);
            $second = substr($this->absoluteMax, $part);

            $valLength = strlen((string)$value);
            $start = $valLength - $part;
            $valFirst = substr((string)$value, 0, $start);
            $valSecond = substr((string)$value, $start);

            if ($valFirst > $first || ($valFirst === $first && $valSecond > $valSecond)) {
                return $this->value = $this->absoluteMax;
            }

            $maxValLength = strlen((string)$this->max);
            $startMax = $maxValLength - $part;
            $maxValFirst = substr((string)$this->max, 0, $startMax);
            $maxValSecond = substr((string)$this->max, $startMax);

            if ($valFirst > $maxValFirst || ($valFirst === $maxValFirst && $valSecond > $maxValSecond)) {
                return $this->value = (string)$value;
            }
        }

        return $this->value = (int)$value;
    }

}
