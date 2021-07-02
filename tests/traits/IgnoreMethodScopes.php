<?php

namespace Tests\Traits;

use Core\Utilities\Functional\Pure;
use Exception;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait IgnoreMethodScopes
 *
 * @package Tests\traits
 */
trait IgnoreMethodScopes
{
    /**
     * In order to test non-public methods, this method may be invoked to treat the method as public. Will return a copy
     * of the otherwise inaccessible method.
     *
     * @param mixed $class
     * @param string $methodName
     * @param mixed ...$classArgs
     *
     * @return callable
     */
    public function accessNonPublicMethod(mixed $class, string $methodName, ...$classArgs): callable
    {
        $className = $class;
        if (is_string($class)) {
            $class = new $className(...$classArgs);
        } else {
            $className = get_class($class);
        }
        try {
            $method = new ReflectionMethod($className, $methodName);
            $method->setAccessible(true);
            // The returned method signature will match that of the provided method name yet having public scope.
            return function (...$args) use ($class, $method) {
                return $method->invokeArgs($class, $args);
            };
        } catch (ReflectionException $e) {
            Pure::trace('Unable to pipe reflection')($e);
            return function () use ($className, $methodName) {
                throw new Exception("Failed to access $methodName of $className");
            };
        }
    }
}
