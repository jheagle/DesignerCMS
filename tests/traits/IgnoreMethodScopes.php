<?php

namespace Core\Tests\Traits;

use Core\Utilities\Functional\Pure;
use ReflectionMethod;

/**
 * Trait IgnoreMethodScopes
 *
 * @package Core\Tests\traits
 */
trait IgnoreMethodScopes
{
    /**
     * In order to test non-public methods, this method may be invoked to treat the method as public. Will return a copy
     * of the otherwise inaccessible method.
     *
     * @param string $className
     * @param string $methodName
     * @param mixed ...$classArgs
     *
     * @return callable
     */
    public function accessNonPublicMethod(string $className, string $methodName, ...$classArgs): callable
    {
        try {
            $method = new ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            Pure::trace('Unable to apply reflection')($e);
        }
        $method->setAccessible(true);

        $classInstance = new $className(...$classArgs);

        // The returned method signature will match that of the provided method name yet having public scope.
        return function (...$args) use ($classInstance, $method) {
            return $method->invokeArgs($classInstance, $args);
        };
    }
}
