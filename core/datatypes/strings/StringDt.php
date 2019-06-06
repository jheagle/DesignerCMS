<?php

namespace Core\DataTypes\Strings;

use Core\DataTypes\DataType;

/**
 * Class StringDt
 *
 * @package Core\DataTypes\Strings
 */
class StringDt extends DataType
{

    protected static $charSet;

    protected $primitiveType = 'string';

    /**
     *
     * @param mixed $value
     * @param array $settings
     */
    public function __construct($value, array $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'charSet' => 'UTF-8',
            ],
            $settings
        ));
        self::setValue($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = mb_convert_encoding($value, self::$charSet);
    }

}