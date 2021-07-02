<?php

namespace Tests\Mocks;

/**
 * Class GenericClass
 *
 * @package Tests\mocks
 */
class GenericClass
{
    /**
     * GenericClass constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Retrieve dynamically assigned property.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name ?? null;
    }

    /**
     * Set dynamic property
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Call dynamic method.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func($this->$name, ...$arguments);
    }
}