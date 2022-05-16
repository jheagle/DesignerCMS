<?php

namespace Core\Objects;

use ArrayAccess;
use Core\Contracts\Arrayable;
use Core\Contracts\Castable;
use Core\Contracts\Jsonable;
use Core\Traits\LazyAssignment;
use Core\Traits\MakeCastable;
use Core\Utilities\Functional\Pure;

/**
 * Class DataTransferObject simplifies creating defined object structures.
 *
 * @package Core\Objects
 */
abstract class DataTransferObject implements ArrayAccess, Arrayable, Castable, Jsonable
{
    use LazyAssignment;
    use MakeCastable;

    public array $extraAttributes = [];

    /**
     * DataTransferObject constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->applyMemberSettings($properties);
    }

    public static function fromArray(array $properties = []): static
    {
        return new static($properties);
    }

    public static function fromJson(string $json = ''): static
    {
        return new static(json_decode($json, true));
    }

    /**
     * Check if the given offset exists on this object.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    final public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset) || array_key_exists($offset, $this->extraAttributes);
    }

    /**
     * Retrieve a value by given offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    final public function offsetGet(mixed $offset): mixed
    {
        return Pure::coalesce(null, Pure::dotGet($this, $offset), Pure::dotGet($this->extraAttributes, $offset));
    }

    /**
     * Set a value on this object by offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    final public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->extraAttributes[] = $value;
            return;
        }
        if (!property_exists($this, $offset)) {
            $this->extraAttributes[$offset] = $value;
            return;
        }
        $this->$offset = $value;
    }

    /**
     * Remove a value from this object by offset.
     *
     * @param mixed $offset
     */
    final public function offsetUnset(mixed $offset): void
    {
        if (property_exists($this, $offset)) {
            $this->$offset = null;
        }
        if (array_key_exists($offset, $this->extraAttributes)) {
            unset($this->extraAttributes[$offset]);
        }
    }

    final public function toArray(): array
    {
        return array_map(
            fn($propertyValue) => $propertyValue instanceof Arrayable ? $propertyValue->toArray() : $propertyValue,
            $this->getAllMembers()
        );
    }

    final public function toJson(): string
    {
        return json_encode(
            array_map(
                fn($propertyValue) => $propertyValue instanceof Arrayable ? $propertyValue->toArray() : $propertyValue,
                $this->getAllMembers()
            )
        );
    }
}