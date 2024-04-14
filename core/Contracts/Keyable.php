<?php

namespace Core\Contracts;

/**
 * Objects which are part of a collection and having an identifier are keyable thereby they have a key and can be retrieve by the key.
 */
interface Keyable
{
    /**
     * Retrieve the identifier for this object.
     * @return int|string
     */
    public function getKey(): int|string;

    /**
     * Use an identifier and retrieve and instance of this associated object.
     * @param int|string $key
     * @return Keyable|null
     */
    public static function fromKey(int|string $key): ?Keyable;
}
