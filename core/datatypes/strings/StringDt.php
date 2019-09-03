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

    const CHARSET_UTF8 = 'UTF-8';
    const CHARSETS = [
        self::CHARSET_UTF8,
    ];
    protected $charSet;

    /**
     *
     * @param string $value
     * @param array $settings
     */
    public function __construct(string $value = '', array $settings = [])
    {
        parent::__construct($value, array_merge(
            [
                'charSet' => self::CHARSET_UTF8,
                'primitiveType' => self::PRIMITIVE_STRING,
            ],
            $settings
        ));
    }

    /**
     * @return mixed
     */
    public function getCharSet()
    {
        return $this->charSet;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|void
     */
    public function setValue($value)
    {
        $this->value = mb_convert_encoding($value, $this->charSet);
    }
}
