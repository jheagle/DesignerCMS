<?php

namespace Core\Traits;

use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Objects\DataTypes\CastedClassType;
use Core\Utilities\Functional\Pure;
use ReflectionClass;
use ReflectionException;
use Throwable;

trait MakeThrowableAdaptor
{
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
     * Wrap this class around an existing exception.
     *
     * @param Throwable $throwable
     *
     * @return $this
     */
    final public function wrap(Throwable $throwable): self
    {
        $this->classInstance = $throwable;
        return $this;
    }
}