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
    public const CHARSET_ASCII = 'ASCII';
    public const CHARSET_UTF8 = 'UTF-8';
    public const CHARSETS = [
        self::CHARSET_ASCII,
        self::CHARSET_UTF8,
    ];
    protected ?string $charSet;

    /**
     * StringDt constructor.
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
        self::setValue($value);
    }

    /**
     * Retrieve the configured character set for this DataType
     *
     * @return mixed
     */
    public function getCharSet()
    {
        return $this->charSet;
    }

    /**
     * Retrieve the value which was set for this DataType
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Update the value for this DataType (apply the correct character set)
     *
     * @param mixed $value
     *
     * @return mixed|void
     */
    public function setValue($value)
    {
        $this->value = mb_convert_encoding($value, $this->charSet);
    }
}
