<?php

namespace Core\Utilities\Functional;

/**
 * Trait PureTrait
 *
 * @package Core\Utilities\Functional
 *
 * @method static callable apply(callable[] ...$fns)
 * @method static callable curry(string | callable $fn, string | object $class = __CLASS__)
 * @method static callable trace(string $label = '')
 * @method static callable tt(string $label = '')
 */
trait PureTrait
{
    public static function extractFunctions()
    {
        return static::importFunctions(true);
    }

    private static function importFunctions(bool $declareGlobal = false)
    {
        $path = __DIR__ . '/functions/';
        return array_reduce(array_filter(scandir($path), function (string $file): string {
            return !preg_match('/^(.){1,2}$/', $file);
        }), function ($functions, string $function) use ($path, $declareGlobal): array {
            $functionName = basename($path . $function, '.php');
            $$functionName = $GLOBALS[$functionName] ?? $functionName;
            if (static::functionDefined($function)) {
                include_once $path . $function;
            }
            $functions[(string)$functionName] = $$functionName;
            return $functions;
        }, []);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private static function functionDefined(string $name): bool
    {
        return !(
            function_exists($name)
            || method_exists(self::class, $name)
            || $name === Pure::RESET_FUNCTIONS
            || array_key_exists($name, $GLOBALS)
        );
    }
}
