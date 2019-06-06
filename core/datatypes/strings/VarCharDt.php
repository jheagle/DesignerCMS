<?php

namespace Core\DataTypes\Strings;

/**
 * Class VarCharDt
 *
 * @package Core\DataTypes\Strings
 */
class VarCharDt extends StringDt
{

    protected $min;

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
        self::setMin();
        self::setMax();
        if ($settings['length'] === null) {
            $settings['length'] = $this->max;
        }
        self::setLength($settings['length']);
        self::setValue($this->value);
    }

    protected function setMin()
    {
        $this->min = 0;
    }

    protected function setMax()
    {
        if ($this->bits >= self::$systemMaxBits) {
            $this->max = (int)((1 << self::$systemMaxBits - 1) - 1);
        } else {
            $this->max = (int)((1 << $this->bits) - 1);
        }
    }

    public function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        if ($length < $this->min) {
            $length = (int)$this->min;
        } elseif ($length > $this->max) {
            $length = (int)$this->max;
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
