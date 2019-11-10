<?php

namespace Core\Utilities\Functional;

/**
 * Class Pure
 *
 * @package Core\Utilities\Functional
 *
 * @method static callable[] extractAll()
 *
 * @method static int add(int $x, int $y)
 * @method static mixed|null coalesce($filter = null, ...$values)
 * @method static callable curry(callable | string | array $fn, int $minArgs = -1, array $args = [])
 * @method static mixed defaultValue($default, $value)
 * @method static number maxBound($max = 0, $number = 0)
 * @method static number minBound($min = 0, $number = 0)
 * @method static int negate(int $number)
 * @method static callable pipe(callable[] ...$fns)
 * @method static int requiredParameterCount (callable | string | array $fn)
 * @method static callable trace(string $label = '')
 * @method static callable tt(string $label = '')
 */
class Pure
{
    use PureTrait;

    /** @var null|Pure $instance */
    private static $instance = null;

    /** @var string $extractFunction */
    private static $extractFunction = 'extractAll';

    /**
     * Pure constructor, we do not need public access to the constructor. This class is instantiated using the
     * instantiatePure method.
     */
    protected function __construct()
    {
    }

    /**
     * Create a reusable instance of Pure.
     *
     * @return \Core\Utilities\Functional\Pure
     */
    final public static function instantiatePure(): Pure
    {
        self::$instance = new Pure();
        return self::$instance;
    }

    /**
     * Retrieve a reference to one of the imported functions.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \BadFunctionCallException
     */
    public static function getFunction(string $name): callable
    {
        if (!is_callable(self::$instance->importedFunctions[$name] ?? false)) {
            throw new \BadFunctionCallException();
        }
        return self::$instance->importedFunctions[$name];
    }

    /**
     * Use Pure to statically call any of the helper functions in the functions directory. This method will import and
     * pass the function call into to real function then return the result of calling that function.
     *
     * @param string $name
     * @param array|mixed $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, array $arguments)
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
        if (!array_key_exists($name, self::$instance->importedFunctions)) {
            self::$instance->importedFunctions = self::$instance->importFunctions();
        }
        return self::getFunction($name)(...$arguments);
    }
}