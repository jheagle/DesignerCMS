<?php

namespace Core\Contracts;

/**
 * Interface Jsonable must be able to provide a json string of this class.
 *
 * @package Core\Contracts
 */
interface Jsonable
{
    /**
     * Create and instance of this class using properties defined in JSON.
     *
     * @param string $json
     *
     * @return Jsonable
     */
    public static function fromJson(string $json): Jsonable;

    /**
     * Output a JSON string of this classes properties.
     *
     * @return string
     */
    public function toJson(): string;
}