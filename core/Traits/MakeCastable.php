<?php

namespace Core\Traits;

use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\Castable;
use Core\DataTypes\Exceptions\IncompatibleCastError;
use Core\Objects\DataTypes\CastedClassType;
use Core\Utilities\Functional\Pure;
use ReflectionClass;
use ReflectionException;

trait MakeCastable
{
    /**
     * Store the original information about this class.
     *
     * @var CastedClassType|null $castedClassType
     */
    protected ?CastedClassType $castedClass = null;

    /**
     * Select the best way to cast the original class to the 'cast to' class. Optionally use the original as-is
     * if it is not castable when the flag is set.
     *
     * @param mixed $original
     * @param mixed $castTo
     * @param bool $useOriginal
     *
     * @return mixed
     */
    public static function applyCast(mixed $original, mixed $castTo, bool $useOriginal = false): mixed
    {
        if ($original instanceof Castable) {
            return $original->castTo($castTo);
        }
        if ($castTo instanceof Castable) {
            return $castTo::wrapCast($original);
        }
        if ($useOriginal) {
            return $original;
        }
        $originalClass = is_null($original) ? 'NULL' : get_class($original);
        $castToClass = is_null($castTo) ? 'NULL' : $castTo;
        throw new IncompatibleCastError($originalClass . ' to ' . $castToClass . 'conversion');
    }

    /**
     * Apply a set of properties from another class to this class.
     *
     * @param mixed $castable
     *
     * @return $this
     */
    final public function assignTransferableProperties(mixed $castable): static
    {
        $properties = [];
        try {
            $properties = (new ReflectionClass($castable))->getProperties();
        } catch (ReflectionException $e) {
            Logger::debug('Failed to get properties', ['Exception' => $e]);
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
     * Cast this class to the class provided.
     *
     * @param mixed $className
     *
     * @return mixed
     */
    final public function castTo(mixed $className): mixed
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
     * Retrieve all of the eligible properties from this class to be applied to another class.
     *
     * @return array
     */
    final public function getTransferableProperties(): array
    {
        $members = $this->getAllMembers();
        $castedProperties = $members['castedClass']->classProperties;
        unset($members['castedClass']);
        return array_replace_recursive($castedProperties, $members);
    }

    /**
     * Retrieve the wrapped class that this class originally casted.
     *
     * @return mixed
     */
    final public function preCast(): mixed
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
     * Set a class to be wrapped by this class.
     *
     * @param mixed $castable
     *
     * @return Castable
     */
    public static function wrapCast(mixed $castable): Castable
    {
        $className = get_called_class();
        $properties = $castable instanceof Castable
            ? $castable->getTransferableProperties()
            : array_replace_recursive(get_class_vars(get_class($castable)), get_object_vars($castable));
        return (new $className(
            ...array_values(
                Pure::buildParameters($className, '__construct', ...$properties)
            )
        ))->assignTransferableProperties($castable);
    }
}