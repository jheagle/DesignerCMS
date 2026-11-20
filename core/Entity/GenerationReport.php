<?php

namespace Core\Entity;

use Core\Objects\DataTransferObject;

/**
 * What one ModelGenerator run did.
 *
 * @package Core\Entity
 */
class GenerationReport extends DataTransferObject
{
    /**
     * Base classes written (new, or the table changed).
     *
     * @var string[]
     */
    public array $written = [];

    /**
     * Base classes that already matched their table.
     *
     * @var string[]
     */
    public array $unchanged = [];

    /**
     * Developer classes created (they didn't exist yet).
     *
     * @var string[]
     */
    public array $created = [];

    /**
     * Developer classes already there, left as they are.
     *
     * @var string[]
     */
    public array $kept = [];

    /**
     * Tables not generated, with the reason.
     *
     * @var array<string, string>
     */
    public array $skipped = [];

    /**
     * Base classes whose table wasn't generated (dropped or renamed): left for the developer to remove.
     *
     * @var string[]
     */
    public array $stale = [];
}
