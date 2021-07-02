<?php

namespace Core\Contracts;

/**
 * Interface Arrayable must provide accessors for creating and outputting as array.
 *
 * @package Core\Contracts
 */
interface Arrayable
{
    /**
     * Create and instance of this class based on an array of properties.
     *
     * @param array $properties
     *
     * @return Arrayable
     */
    public static function fromArray(array $properties): Arrayable;

    /**
     * Retrieve an array of properties from this class.
     *
     * @return array
     */
    public function toArray(): array;
}