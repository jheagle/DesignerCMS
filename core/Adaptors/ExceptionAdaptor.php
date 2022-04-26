<?php

namespace Core\Adaptors;

use Core\Contracts\Castable;
use Core\Contracts\ThrowableAdaptor;
use Core\Traits\LazyAssignment;
use Core\Traits\MakeAdaptable;
use Core\Traits\MakeCastable;
use Core\Traits\MakeThrowableAdaptor;
use Core\Utilities\Functional\Pure;
use Exception;

/**
 * Class ExceptionAdaptor is used as the parent to wrap vendor exceptions.
 *
 * @package Core\Adaptors
 */
abstract class ExceptionAdaptor extends Exception implements ThrowableAdaptor
{
    use LazyAssignment;
    use MakeAdaptable;
    use MakeCastable;
    use MakeThrowableAdaptor;

    /**
     * Wrap another class within this class.
     *
     * @param mixed $castable
     *
     * @return Castable
     */
    final public static function wrapCast(mixed $castable): Castable
    {
        $className = get_called_class();
        self::$customInstances[$className] = $castable;
        $properties = $castable instanceof Castable
            ? $castable->getTransferableProperties()
            : array_replace_recursive(get_class_vars(get_class($castable)), get_object_vars($castable));
        return (new $className(
            ...array_values(
                Pure::buildParameters($className, '__construct', ...$properties)
            )
        ))
            ->assignTransferableProperties($castable)
            ->wrap($castable);
    }
}