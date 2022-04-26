<?php

namespace Core\Contracts;

use Throwable;

interface Adaptable
{
    /**
     * Create a new instance of this adaptor.
     *
     * @param mixed|null $resource
     *
     * @return Adaptable
     */
    public static function instantiate(mixed $resource = null): Adaptable;

    /**
     * Initialize this adaptor with a single instance that can be used statically.
     *
     * @param mixed|null $resource
     *
     * @return Adaptable
     */
    public static function initialize(mixed $resource = null): Adaptable;

    /**
     * Clear any statically set attributes associated with this child class.
     *
     * @return Adaptable
     */
    public static function reset(): Adaptable;

    /**
     * Predefine the resource to be used for the wrapped instance, useful for testing.
     *
     * @param mixed $resource
     *
     * @return Adaptable|null
     */
    public static function setResource(mixed $resource): ?Adaptable;

    /**
     * Create a single reusable instance of this adaptor.
     *
     * @param mixed|null $resource
     * @param ...$args
     *
     * @return Adaptable
     */
    public static function singleton(mixed $resource = null, ...$args): Adaptable;

    /**
     * Instantiate the class instance wrapped by this adaptor.
     *
     * @return $this
     */
    public function build(): self;

    /**
     * Catch a throwable and wrap it in the correct adaptor class.
     *
     * @param callable $riskyCall
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function useThrowable(callable $riskyCall): mixed;

    /**
     * Provide constructor parameters that will be used when build is called to instantiate the wrapped class.
     *
     * @param ...$args
     *
     * @return $this
     */
    public function with(...$args): self;

    /**
     * Call dynamic method.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed;

    /**
     * Call dynamic static method.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed;

    /**
     * Retrieve dynamically assigned property.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name): mixed;

    /**
     * Make this adaptor callable, which will call the wrapped class if applicable.
     *
     * @param ...$args
     *
     * @return mixed
     */
    public function __invoke(...$args): mixed;

    /**
     * Set dynamic property.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void;
}