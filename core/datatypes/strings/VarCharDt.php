<?php

namespace Core\DataTypes\Strings;

use Core\Utilities\Functional\Pure;

/**
 * Class VarCharDt
 *
 * @package Core\DataTypes\Strings
 */
class VarCharDt extends StringDt
{
    protected $min = 0;
    protected $max;
    protected $bits = 16;
    protected $length;

    public function __construct($value, $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'length' => null,
                'charSet' => 'UTF-8',
            ],
            $settings
        ));
        self::setMax();
        self::setLength($settings['length'] ?? null);
        self::setValue($this->value);
    }

    protected function setMax()
    {
        if ($this->bits >= self::$systemMaxBits) {
            $this->max = (int)((1 << self::$systemMaxBits - 1) - 1);
            return $this->max;
        }
        $this->max = (int)((1 << $this->bits) - 1);
        return $this->max;
    }

    public function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        $this->length = Pure::pipe(
            Pure::curry([Pure::class, 'nullCoalesce'])($this->max),
            Pure::curry([Pure::class, 'minBound'])((int)$this->min),
            Pure::curry([Pure::class, 'maxBound'])((int)$this->max)
        )($length);
        return $this->length;
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
