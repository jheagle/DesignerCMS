<?php

namespace Core\Traits;

use Core\Adaptors\Config;
use Core\Adaptors\Lang;
use Core\Contracts\Adaptable;
use Core\Contracts\Castable;
use Core\Utilities\Functional\Pure;
use Error;
use Throwable;

trait MakeAdaptable
{
    /**
     * Control properties accessible via __get and __set, explicit 'get' / 'set' with true allows both read and write.
     *
     * @var array|bool[][] $accessScopes
     */
    protected array $accessScopes = [
        'accessScopes' => ['get' => true],
        'castedClass' => ['get' => true],
        'classInstance' => ['get' => true],
        'customInstance' => ['get' => true],
    ];

    /**
     * This is the instance wrapped by the adaptor, it may implement __invoke and be callable.
     *
     * @var callable|object|string|null $classInstance
     */
    protected mixed $classInstance = null;

    /**
     * Arguments passed in using with are formatted and stored as constructorArgs
     *
     * @var array $constructorArgs
     */
    protected array $constructorArgs = [];

    /**
     * Override the instance that will be used by storing it on this array which is keyed by the child class name.
     *
     * @var array $customInstances
     */
    protected static array $customInstances = [];

    /**
     * Store a singular instance of this child class on this array keyed by the child classes name.
     *
     * @var array $staticInstances
     */
    protected static array $staticInstances = [];

    /**
     * Create a new instance of this adaptor.
     *
     * @param mixed|null $resource
     *
     * @return self
     */
    public static function instantiate(mixed $resource = null): self
    {
        if (!is_null($resource)) {
            self::setResource($resource);
        }
        return new static();
    }

    /**
     * Initialize this adaptor with a single instance that can be used statically.
     *
     * @param mixed|null $resource
     *
     * @return Adaptable
     */
    public static function initialize(mixed $resource = null): Adaptable
    {
        if (!is_null($resource)) {
            self::setResource($resource);
        }
        $className = get_called_class();
        if (is_null(Pure::dotGet(self::$staticInstances, $className))) {
            self::$staticInstances[$className] = new static(true);
        }
        return self::$staticInstances[$className];
    }

    /**
     * Clear any statically set attributes associated with this child class.
     *
     * @return Adaptable
     */
    public static function reset(): Adaptable
    {
        $className = get_called_class();
        self::$customInstances[$className] = null;
        self::$staticInstances[$className] = null;
        return static::initialize();
    }

    /**
     * Predefine the resource to be used for the wrapped instance, useful for testing.
     *
     * @param mixed $resource
     *
     * @return Adaptable|null
     */
    public static function setResource(mixed $resource): ?Adaptable
    {
        $className = get_called_class();
        self::$customInstances[$className] = $resource;
        return self::initialize();
    }

    /**
     * Create a single reusable instance of this adaptor.
     *
     * @param mixed|null $resource
     * @param ...$args
     *
     * @return Adaptable
     */
    public static function singleton(mixed $resource = null, ...$args): Adaptable
    {
        if (!is_null($resource)) {
            self::setResource($resource);
        }
        $className = get_called_class();
        if (is_null(Pure::dotGet(self::$staticInstances, $className))) {
            self::$staticInstances[$className] = is_null(Pure::dotGet(self::$customInstances, $className))
                ? (new static())->with($args)->build()
                : new static();
        }
        return self::$staticInstances[$className];
    }

    /**
     * Instantiate the class instance wrapped by this adaptor.
     *
     * @return $this
     */
    final public function build(): self
    {
        $className = get_called_class();
        $this->classInstance = self::$customInstances[$className] ?? new $this->castedClass->className(
                ...array_values($this->constructorArgs)
            );
        return $this;
    }

    /**
     * Catch a throwable and wrap it in the correct adaptor class.
     *
     * @param callable $riskyCall
     *
     * @return mixed
     *
     * @throws Throwable
     */
    final public function useThrowable(callable $riskyCall): mixed
    {
        try {
            return $riskyCall();
        } catch (Throwable $throwable) {
            $throwableClass = get_class($throwable);
            $throwableAdaptor = Pure::dotGet(
                array_flip(Config::get('adaptors.throwable', [])),
                $throwableClass,
                $throwableClass
            );
            throw $throwableAdaptor === $throwableClass ? $throwable : $throwableAdaptor::wrapCast($throwable);
        }
    }

    /**
     * Provide constructor parameters that will be used when build is called to instantiate the wrapped class.
     *
     * @param ...$args
     *
     * @return $this
     */
    final public function with(...$args): self
    {
        $this->constructorArgs = array_map(
            fn(mixed $arg) => is_array($arg) ? array_map(
                fn(mixed $a) => $a instanceof Castable ? $a->preCast() : $a,
                $arg
            ) : $arg,
            Pure::buildParameters($this->castedClass->className, '__construct', ...$args)
        );
        return $this;
    }

    /**
     * Call dynamic method.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        $className = get_class($this);
        if (method_exists($className, $name)) {
            return $this->$name(
                ...array_values(Pure::buildParameters($className, $name, ...$arguments))
            );
        }
        $instanceClass = $this->classInstance ?? $this->castedClass->className;
        if (method_exists($instanceClass, $name)) {
            return $this->classInstance->$name(
                ...array_values(Pure::buildParameters($this->castedClass->className, $name, ...$arguments))
            );
        }
        if (!is_callable($this->$name)) {
            throw new Error(
                Lang::get('errors.adaptor.undefinedMethod', [get_class($this), $name])
            );
        }
        return call_user_func($this->$name, ...$arguments);
    }

    /**
     * Call dynamic static method.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $className = get_called_class();
        if (method_exists($className, $name)) {
            return self::$staticInstances::$name(
                ...array_values(Pure::buildParameters($className, $name, ...$arguments))
            );
        }
        if (!method_exists(self::$staticInstances[$className]->classInstance, $name)) {
            throw new Error(
                Lang::get('errors.adaptor.undefinedMethod', [$className, $name])
            );
        }
        return self::$staticInstances[$className]->classInstance::$name(
            ...
            array_values(Pure::buildParameters(self::$staticInstances[$className]->classInstance, $name, ...$arguments))
        );
    }

    /**
     * Retrieve dynamically assigned property.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (Pure::dotGet($this->accessScopes, "$name.get")) {
            return $this->$name;
        }
        if (defined("{$this->castedClass->className}::$name")) {
            return constant("$this->classInstance::$name");
        }
        if (is_null(Pure::dotGet($this, "classInstance.$name"))) {
            throw new Error(
                Lang::get('errors.adaptor.inaccessibleProperty.get', [get_class($this), $name])
            );
        }
        return $this->classInstance->$name;
    }

    /**
     * Make this adaptor callable, which will call the wrapped class if applicable.
     *
     * @param ...$args
     *
     * @return mixed
     */
    public function __invoke(...$args): mixed
    {
        if (is_callable($this->classInstance)) {
            return $this->classInstance(...$args);
        }
        throw new Error('The class instance of ' . get_called_class() . ' is not callable');
    }

    /**
     * Set dynamic property.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (Pure::dotGet($this->accessScopes, "$name.set")) {
            $this->$name = $value;
            return;
        }
        if (is_null(Pure::dotGet($this, "classInstance.$name"))) {
            if (!property_exists($this, $name)) {
                $this->accessScopes[$name] = ['get' => true, 'set' => true];
                $this->$name = $value;
                return;
            }
            throw new Error(
                Lang::get('errors.adaptor.inaccessibleProperty.set', [get_class($this), $name])
            );
        }
        $this->classInstance->$name = $value;
    }
}