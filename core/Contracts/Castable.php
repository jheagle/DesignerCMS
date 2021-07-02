<?php

namespace Core\Contracts;

/**
 * Interface Castable identifies classes that can wrap, and unwrap other Castables.
 *
 * @package Core
 */
interface Castable
{
    /**
     * Create casted class from properties and assign them all to the new class.
     *
     * @param mixed $castable
     *
     * @return $this
     */
    public function assignTransferableProperties(mixed $castable): static;

    /**
     * Apply this class to another Castable.
     *
     * @param mixed $className
     *
     * @return mixed
     */
    public function castTo(mixed $className): mixed;

    /**
     * Retrieve the merged properties from casted class and parent class.
     *
     * @return array
     */
    public function getTransferableProperties(): array;

    /**
     * Return an instance of the class prior to being wrapped.
     *
     * @return mixed
     */
    public function preCast(): mixed;

    /**
     * Apply this class to another class by wrapping the other class.
     *
     * @param mixed $castable
     *
     * @return Castable
     */
    public static function wrapCast(mixed $castable): Castable;
}