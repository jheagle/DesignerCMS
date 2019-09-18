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
        $this->value = $value;
    }
}