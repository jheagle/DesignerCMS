<?php

namespace Core\DataTypes;

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
    public function getValue();

    /**
     * Sets the internally stored value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setValue($value);
}