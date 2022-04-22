<?php

namespace Core\DataTypes\Interfaces;

/**
 * Interface DataTypeObject
 *
 * @package Core\DataType\DataType
 */
interface DataTypeObject extends Potential
{
    /**
     * Returns the internally stored value.
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Sets the internally stored value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setValue(mixed $value): mixed;
}