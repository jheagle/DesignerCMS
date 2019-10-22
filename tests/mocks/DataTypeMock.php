<?php

namespace Core\Tests\Mocks;

use Core\DataTypes\DataType;

/**
 * Class DataTypeMock
 *
 * @package Core\Tests\Mocks
 */
class DataTypeMock extends DataType
{
    /**
     * Return the value that was previously set and stored as $this->value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the stored value of this class.
     *
     * @param mixed $value
     *
     * @return mixed|void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}