<?php

namespace Core\Entity\Interfaces;

use Core\Contracts\Comparable;
use Core\DataTypes\Interfaces\Potential;
use Core\Entity\Field;

/**
 * A record of one table: its Fields describe the columns, its values are one row.
 *
 * @package Core\Entity\Interfaces
 */
interface EntityObject extends Comparable, Potential
{
    /**
     * The table this entity's rows live in.
     *
     * @return string
     */
    public static function table(): string;

    /**
     * The columns, keyed by name.
     *
     * @return array<string, Field>
     */
    public static function fields(): array;

    /**
     * Insert the row, or update it when it already has its primary key.
     *
     * @return static
     */
    public function save(): static;

    /**
     * The values, keyed by column name. (Not Arrayable: an entity can't be built from an array alone, it needs its
     * database connection.)
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Delete the row.
     *
     * @return bool Whether a row was deleted.
     */
    public function delete(): bool;
}
