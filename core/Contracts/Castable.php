<?php

namespace Core\Contracts;

/**
 * Interface Castable identifies classes that can wrap, and unwrap other Castables.
 *
 * @package Core\Contracts
 */
interface Castable extends LazyAssignable
{
    /**
     * Select the best way to cast the original class to the 'cast to' class. Optionally use the original as-is
     * if it is not castable when the flag is set.
     *
     * @param mixed $original
     * @param mixed $castTo
     * @param bool $useOriginal
     *
     * @return mixed
     */
    public static function applyCast(mixed $original, mixed $castTo, bool $useOriginal = false): mixed;

    /**
     * Create casted class from properties and assign them all to the new class.
     *
     * @param mixed $castable
     *
     * @return Castable
     */
    public function assignTransferableProperties(mixed $castable): Castable;

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