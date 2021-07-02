<?php

namespace Core\Adaptors;

use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\Castable;
use Core\DataTypes\GenericType;
use Core\Objects\DataTypes\CastedClassType;
use Core\Utilities\Functional\Pure;
use Error;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class Adaptor is the parent class to all Adaptors used to control access to vendor resources.
 *
 * @package Core\Adaptors
 *
 * @method classInstance($args)
 */
abstract class Adaptor extends GenericType
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
     * Adaptor constructor.
     *
     * @param bool $isStatic
     */
    protected function __construct(bool $isStatic = false)
    {
        $thisClass = get_class($this);
        $className = Config::get('adaptors.' . $thisClass, $thisClass);
        $properties = [];
        try {
            $properties = (new ReflectionClass($className))->getProperties();
        } catch (ReflectionException $e) {
            Logger::debug("Failed to get properties for $className", ['Exception' => $e]);
        }
        $this->castedClass = CastedClassType::fromArray(
            [
                'className' => $className,
                'classProperties' => $properties,
            ]
        );
        if ($isStatic) {
            $this->classInstance = self::$customInstances[$thisClass] ?? $className;
        }
    }

    /**
     * Create a new instance of this adaptor.
     *
     * @param mixed|null $resource
     *
     * @return static
     */
    public static function instantiate(mixed $resource = null): static
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
     * @return static
     */
    public static function initialize(mixed $resource = null): static
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
     * @return static
     */
    public static function reset(): static
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
     * @return static|null
     */
    public static function setResource(mixed $resource): ?static
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
     * @return static
     */
    public static function singleton(mixed $resource = null, ...$args): static
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
     * Apply this adaptor to another class by encapsulating it.
     *
     * @param mixed $castable
     *
     * @return static
     */
    public static function wrapCast(mixed $castable): static
    {
        return self::instantiate($castable)
            ->with(
                $castable instanceof Castable
                    ? $castable->getTransferableProperties()
                    : array_replace_recursive(get_class_vars(get_class($castable)), get_object_vars($castable))
            )
            ->build();
    }

    /**
     * Instantiate the class instance wrapped by this adaptor.
     *
     * @return $this
     */
    public function build(): self
    {
        $className = get_called_class();
        $this->classInstance = self::$customInstances[$className] ?? new $this->castedClass->className(
                ...array_values($this->constructorArgs)
            );
        return $this;
    }

    /**
     * Resolve and return the class wrapped by this adaptor.
     *
     * @return mixed
     */
    public function preCast(): mixed
    {
        if (!is_null($this->classInstance)) {
            return $this->classInstance;
        }
        return parent::preCast();
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
    public function useThrowable(callable $riskyCall): mixed
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
    public function with(...$args): self
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
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments): mixed
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
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments): mixed
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
     * @param $name
     *
     * @return mixed
     */
    public function __get($name): mixed
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
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        if (Pure::dotGet($this->accessScopes, "$name.set")) {
            $this->$name = $value;
            return $this;
        }
        if (is_null(Pure::dotGet($this, "classInstance.$name"))) {
            if (!property_exists($this, $name)) {
                $this->accessScopes[$name] = ['get' => true, 'set' => true];
                $this->$name = $value;
                return $this;
            }
            throw new Error(
                Lang::get('errors.adaptor.inaccessibleProperty.set', [get_class($this), $name])
            );
        }
        $this->classInstance->$name = $value;
        return $this;
    }
}