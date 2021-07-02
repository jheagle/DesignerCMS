<?php

namespace Tests\Mocks;

use Core\DataTypes\DataType;

/**
 * Class DataTypeMock
 *
 * @package Tests\Mocks
 */
class DataTypeMock extends DataType
{
    /**
     * Return the value that was previously set and stored as $this->value
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Set the stored value of this class.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setValue(mixed $value): mixed
    {
        $this->value = $value;
        return $value;
    }
}