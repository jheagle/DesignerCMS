<?php

namespace Core\DataTypes\Strings;

/**
 * Class CharDt
 *
 * @package Core\DataTypes\Strings
 */
class CharDt extends VarCharDt
{

    protected $bits = 8;

    public function __construct($value, $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'length' => null,
                'charSet' => 'UTF-8',
            ],
            $settings
        ));
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
