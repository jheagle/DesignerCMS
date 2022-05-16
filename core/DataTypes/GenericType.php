<?php

namespace Core\DataTypes;

use Core\Contracts\Castable;
use Core\Contracts\Declarable;
use Core\Traits\Declarative;
use Core\Traits\LazyAssignment;
use Core\Traits\MakeCastable;

/**
 * Class GenericType can be extended to provided functionality for casting classes.
 *
 * @package Core
 */
abstract class GenericType implements Castable, Declarable
{
    use LazyAssignment;
    use MakeCastable;
    use Declarative;

    public function __toString(): string
    {
        return $this->getClassDescription();
    }
}
