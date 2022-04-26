<?php

namespace Core\DataTypes;

use Core\Contracts\Castable;
use Core\Traits\LazyAssignment;
use Core\Traits\MakeCastable;

/**
 * Class GenericType can be extended to provided functionality for casting classes.
 *
 * @package Core
 */
abstract class GenericType implements Castable
{
    use LazyAssignment;
    use MakeCastable;
}