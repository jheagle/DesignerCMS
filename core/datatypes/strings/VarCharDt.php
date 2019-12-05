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
    protected int $minLength = 0;
    protected ?int $maxLength;
    protected int $bits = 16;
    protected ?int $length;

    /**
     * VarCharDt constructor.
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
                    'length' => null,
                ],
                $settings
            )
        );
        self::setMinLength();
        self::setMaxLength();
        self::setLength($settings['length'] ?? null);
        self::setValue($this->value);
    }

    /**
     * Retrieve the usable bits for this VarChar
     *
     * @return int
     */
    public function getBits(): int
    {
        return $this->bits;
    }

    /**
     * Retrieve the length that was set for this VarChar
     *
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Get the maximum possible length for this VarChar
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    /**
     * Get the minimum possible length for this VarChar
     *
     * @return int
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * Return the value stored as VarChar
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set a value within the constraints of VarChar which can store up to a 16 bit value or as specified by the length.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function setValue($value): string
    {
        if (strlen($value) >= $this->length) {
            trigger_error("Truncating VarChar value: \"{$value}\"", E_USER_WARNING);
        }
        $value = substr($value, 0, $this->length);
        $this->value = $value;
        return $this->value;
    }

    /**
     * Set the max string length that can be used for this stored VarChar value. When no length is provide (null) the
     * default length with be maxLength. Also, ensure the the provided length is within the bounds of min and max
     * length.
     *
     * @param int|null $length
     *
     * @return int
     */
    protected function setLength(?int $length): int
    {
        $this->length = Pure::pipe(
            Pure::curry([Pure::class, 'defaultValue'])($this->maxLength),
            Pure::curry([Pure::class, 'minBound'])((int)$this->minLength),
            Pure::curry([Pure::class, 'maxBound'])((int)$this->maxLength)
        )(
            $length
        );
        return $this->length;
    }

    /**
     * The max length must match the max number that can be stored in set bits or the number of system bits available if
     * less than the set bits.
     *
     * @return int
     */
    protected function setMaxLength(): int
    {
        if ($this->bits >= self::$systemMaxBits) {
            $this->maxLength = (int)((1 << self::$systemMaxBits - 1) - 1);
            if ($this->maxLength < $this->minLength) {
                // Fix the overflow into negative number back to the highest positive number
                $this->maxLength = ~$this->maxLength;
                return $this->maxLength;
            }
            return $this->maxLength;
        }
        $this->maxLength = (int)((1 << $this->bits) - 1);
        return $this->maxLength;
    }

    /**
     * The min length should always be 0, we will ensure it is reset to zero by having this function called in the
     * constructor.
     *
     * @return int
     */
    protected function setMinLength(): int
    {
        $this->minLength = 0;
        return $this->minLength;
    }
}
