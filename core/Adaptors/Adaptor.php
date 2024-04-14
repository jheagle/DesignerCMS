<?php

namespace Core\Adaptors;

use AllowDynamicProperties;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Contracts\Adaptable;
use Core\Contracts\Castable;
use Core\DataTypes\GenericType;
use Core\Objects\DataTypes\CastedClassType;
use Core\Traits\MakeAdaptable;
use ReflectionClass;
use ReflectionException;

/**
 * Class Adaptor is the parent class to all Adaptors used to control access to vendor resources.
 *
 * @package Core\Adaptors
 *
 * @method classInstance(array $args)
 */
#[AllowDynamicProperties]
class Adaptor extends GenericType implements Adaptable
{
    use MakeAdaptable;

    /**
     * Adaptor constructor.
     *
     * @param bool $isStatic
     */
    public function __construct(bool $isStatic = false)
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
}