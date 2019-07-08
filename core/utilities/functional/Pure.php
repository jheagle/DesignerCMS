<?php

namespace Core\Utilities\Functional;

/**
 * Class Pure
 *
 * @package Core\Utilities\Functional
 *
 * @method static callable[] extractAll()
 *
 * @method static callable apply(callable[] ...$fns)
 * @method static callable curry(string | callable $fn, string | object $class = __CLASS__)
 * @method static callable trace(string $label = '')
 * @method static callable tt(string $label = '')
 */
class Pure
{
    use PureTrait;

    /** @var null|Pure */
    private static $instance = null;

    /**
     * @var string
     */
    private static $extractFunction = 'extractAll';

    /**
     * Pure constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return \Core\Utilities\Functional\Pure
     */
    final public static function instantiatePure(): Pure
    {
        self::$instance = new Pure();
        return self::$instance;
    }

    /**
     * @param string $name
     * @param array|mixed $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (is_null(self::$instance)) {
            self::instantiatePure();
        }
        if ($name === self::$extractFunction) {
            $name = 'extractFunctions';
        }
        if (method_exists(self::$instance, $name)) {
            return self::$instance->$name(...$arguments);
        }
        if (array_key_exists($name, self::$instance->importedFunctions)) {
            $importedFunctions = self::$instance->importedFunctions;
            return $importedFunctions[$name](...$arguments);
        }
        $importedFunctions = self::$instance->importFunctions();
        return array_key_exists($name, self::$instance->importedFunctions)
            ? $importedFunctions[$name](...$arguments)
            : false;
    }
}