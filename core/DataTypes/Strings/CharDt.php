<?php

namespace Core\DataTypes\Strings;

/**
 * Class CharDt
 *
 * @package Core\DataTypes\Strings
 */
class CharDt extends VarCharDt
{
    protected int $bits = 8;

    /**
     * CharDt constructor.
     *
     * @param string $value
     * @param array $settings
     */
    public function __construct(string $value = '', array $settings = [])
    {
        parent::__construct(
            $value,
            array_merge(
                [
                    'length' => 1,
                ],
                $settings
            )
        );
        self::setValue($this->value);
    }

    /**
     * Return the value stored as Char with the right padding removed.
     *
     * @return string
     */
    public function getValue(): string
    {
        return rtrim($this->value);
    }

    /**
     * Set a value within the constraints of Char which can store up to a 8 bit value or as specified by the length.
     * The value will be right padded with blanks.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function setValue(mixed $value): string
    {
        // Note: All values passed to CharDt exceeding the length will be truncated
        $value = substr($value, 0, $this->length);
        $this->value = $value;
        $this->value = str_pad($value, $this->length, "\x0B", STR_PAD_RIGHT);
        return $this->value;
    }
}
