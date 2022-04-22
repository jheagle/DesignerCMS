<?php

namespace Core\Adaptors;

use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\Castable;
use Core\Contracts\LazyAssignable;
use Core\Objects\DataTypes\CastedClassType;
use Core\Traits\LazyAssignment;
use Core\Utilities\Functional\Pure;
use Error;
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class ExceptionAdaptor is used as the parent to wrap vendor exceptions.
 *
 * @package Core\Adaptors
 */
abstract class ExceptionAdaptor extends Exception implements Castable, LazyAssignable
{
    use LazyAssignment;

    protected ?CastedClassType $castedClass = null;
    protected mixed $classInstance = null;
    protected static array $customInstances = [];

    /**
     * ExceptionAdaptor constructor.
     *
     * @param string $message
     * @param mixed ...$args
     */
    public function __construct(string $message, ...$args)
    {
        $thisClass = get_class($this);
        $className = Config::get('adaptors.throwable.' . $thisClass, $thisClass);
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
        parent::__construct(
            $message,
            Pure::dotGet($args, 'code', 0),
            Pure::dotGet($args, 'previous')
        );
        if (!is_null(Pure::dotGet(self::$customInstances, $thisClass))) {
            $this->classInstance = self::$customInstances[$thisClass];
            return;
        }
        $constructorArgs = Pure::buildParameters($className, '__construct', [$message, ...$args]);
        $constructorArgs['message'] = $message;
        $this->classInstance = new $className(...array_values($constructorArgs));
    }

    /**
     * Clear static properties for the child class.
     */
    public static function reset(): void
    {
        $className = get_called_class();
        self::$customInstances[$className] = null;
    }

    /**
     * Pre-define a custom resource to be used for this class.
     *
     * @param mixed $resource
     */
    public static function setResource(mixed $resource): void
    {
        $className = get_called_class();
        self::$customInstances[$className] = $resource;
    }

    /**
     * Wrap another class within this class.
     *
     * @param mixed $castable
     *
     * @return Castable
     */
    public static function wrapCast(mixed $castable): Castable
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

    /**
     * Apply the properties of a given class to this class.
     *
     * @param mixed $castable
     *
     * @return $this
     */
    public function assignTransferableProperties(mixed $castable): static
    {
        $properties = [];
        try {
            $properties = (new ReflectionClass($castable))->getProperties();
        } catch (ReflectionException $e) {
            Logger::debug('Failed to get properties for ' . get_class($castable), ['Exception' => $e]);
        }
        $this->castedClass = CastedClassType::fromArray(
            [
                'className' => get_class($castable),
                'classProperties' => $properties,
            ]
        );
        if ($castable instanceof Castable) {
            $this->applyMemberSettings($castable->getTransferableProperties());
        }
        return $this;
    }

    /**
     * Cast this class to another provided class.
     *
     * @param mixed $className
     *
     * @return mixed
     */
    public function castTo(mixed $className): mixed
    {
        $preCastName = $this->castedClass->className;
        if ($className === $preCastName || in_array($className, class_implements($preCastName))) {
            return $this->preCast();
        }
        $newClass = new $className(
            ...array_values(
                Pure::buildParameters($className, '__construct', ...$this->getTransferableProperties())
            )
        );
        if ($newClass instanceof Castable) {
            $newClass->assignTransferableProperties($this);
        }
        return $newClass;
    }

    /**
     * Retrieve all of this classes properties which are eligible to be transferred to another class.
     *
     * @return array
     */
    public function getTransferableProperties(): array
    {
        $members = $this->getAllMembers();
        $castedProperties = $members['castedClass']->classProperties;
        unset($members['castedClass']);
        return array_replace_recursive($castedProperties, $members);
    }

    /**
     * Retrieve the original class that was previously wrapped in this class during casting.
     *
     * @return mixed
     */
    public function preCast(): mixed
    {
        if (!is_null($this->classInstance)) {
            return $this->classInstance;
        }
        $newClass = new $this->castedClass->className(
            ...array_values(
                Pure::buildParameters(
                    $this->castedClass->className,
                    '__construct',
                    ...$this->castedClass->classProperties->toArray()
                )
            )
        );
        if ($newClass instanceof Castable) {
            $newClass->assignTransferableProperties($this);
        }
        return $newClass;
    }

    /**
     * Wrap this class around an existing exception.
     *
     * @param Throwable $throwable
     *
     * @return $this
     */
    public function wrap(Throwable $throwable): self
    {
        $this->classInstance = $throwable;
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
        $method = Pure::dotGet($this, "classInstance.$name");
        if (!is_callable($method)) {
            throw new Error(
                Lang::get('errors.adaptor.undefinedMethod', [get_class($this), $name])
            );
        }
        return call_user_func($method, ...$arguments);
    }
}