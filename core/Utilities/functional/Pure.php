<?php

namespace Core\Utilities\Functional;

use BadFunctionCallException;

/**
 * Class Pure
 *
 * @package Core\Utilities\Functional
 *
 * @method static callable[] extractAll()
 *
 * @method static int add(int $x, int $y)
 * @method static array buildParameters(string $className, string $method, ...$args)
 * @method static mixed|null castTo(mixed $value, string $castType)
 * @method static mixed|null coalesce(mixed $filter = null, ...$values)
 * @method static callable curry(callable | string | array $fn, int $minArgs = -1, array $args = [])
 * @method static mixed defaultValue(mixed $default, mixed $value)
 * @method static mixed|null dotGet(array|object $arrayObject, string $dotNotation, mixed $default = null)
 * @method static mixed|null dotSet(array|object $arrayObject, string $dotNotation, mixed $value)
 * @method static mixed envGet (string $name, mixed $default = null)
 * @method static number maxBound(float|int $max = 0, float|int $number = 0)
 * @method static number minBound(float|int $min = 0, float|int $number = 0)
 * @method static int negate(int $number)
 * @method static callable pipe(callable[] ...$fns)
 * @method static int requiredParameterCount (callable | string | array $fn)
 * @method static string strAfter(string $subject, string $search)
 * @method static string strAfterLast(string $subject, string $search)
 * @method static string strBefore(string $subject, string $search)
 * @method static string strBeforeLast(string $subject, string $search)
 * @method static callable trace(string $label = '')
 * @method static callable tt(string $label = '')
 */
class Pure
{
    use PureTrait;

    private static ?Pure $instance = null;

    private static string $extractFunction = 'extractAll';

    /**
     * Pure constructor, we do not need public access to the constructor. This class is instantiated using the
     * instantiatePure method.
     */
    private function __construct()
    {
    }

    /**
     * Create a reusable instance of Pure.
     *
     * @return Pure
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
     * @throws BadFunctionCallException
     */
    public static function getFunction(string $name): callable
    {
        if (!is_callable(self::$instance->importedFunctions[$name] ?? false)) {
            throw new BadFunctionCallException();
        }
        return self::$instance->importedFunctions[$name];
    }

    /**
     * Use Pure to statically call any of the helper functions in the functions directory. This method will import and
     * pass the function call into to real function then return the result of calling that function.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
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
