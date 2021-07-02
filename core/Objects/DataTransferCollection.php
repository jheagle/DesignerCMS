<?php

namespace Core\Objects;

use ArrayAccess;
use Core\Contracts\Arrayable;
use Core\Contracts\Jsonable;
use Core\Utilities\Functional\Pure;

/**
 * Class DataTransferCollection stores a collection of DataTransferObjects.
 *
 * @package Core\Objects
 */
abstract class DataTransferCollection implements ArrayAccess, Arrayable, Jsonable
{
    public array|ArrayAccess $collection = [];

    public function __construct(array $collectionObjects = [])
    {
        $this->collection = $collectionObjects;
    }

    public static function fromArray(array $properties): self
    {
        return new static($properties);
    }

    public static function fromJson(string $json): self
    {
        return new static(json_decode($json, true));
    }

    public function offsetExists($offset): bool
    {
        return !is_null(Pure::dotGet($this->collection, $offset));
    }

    public function offsetGet($offset): mixed
    {
        return Pure::dotGet($this->collection, $offset);
    }

    public function offsetSet($offset, $value): void
    {
        Pure::dotSet($this->collection, $offset, $value);
    }

    public function offsetUnset($offset): void
    {
        if (!is_null(Pure::dotGet($this->collection, $offset))) {
            unset($this->collection[$offset]);
        }
    }

    public function toArray(): array
    {
        return array_map(
            fn($propertyValue) => $propertyValue instanceof Arrayable ? $propertyValue->toArray() : $propertyValue,
            $this->collection
        );
    }

    public function toJson(): string
    {
        return json_encode(
            array_map(
                fn($propertyValue) => $propertyValue instanceof Arrayable ? $propertyValue->toArray() : $propertyValue,
                $this->collection
            )
        );
    }
}