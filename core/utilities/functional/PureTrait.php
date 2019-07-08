<?php

namespace Core\Utilities\Functional;

/**
 * Trait PureTrait
 *
 * @package Core\Utilities\Functional
 */
trait PureTrait
{
    protected $importedFunctions = [];

    /**
     * @return callable[]
     */
    protected function extractFunctions(): array
    {
        return $this->importFunctions(true);
    }

    /**
     * @param bool $declareGlobal
     *
     * @return callable[]
     */
    protected function importFunctions(bool $declareGlobal = false): array
    {
        $path = __DIR__ . '/functions/';
        $this->importedFunctions += array_reduce(
            array_filter(
                scandir($path),
                function ($file) use ($path) {
                    $functionName = basename($path . $file, '.php');
                    return !(preg_match('/^(.){1,2}$/', $file) || $this->functionDefined($functionName));
                }
            ),
            function (array $functions, string $function) use ($path, $declareGlobal): array {
                $functionName = basename($path . $function, '.php');
                $$functionName = $GLOBALS[$functionName] ?? $functionName;
                include $path . $function;
                if (is_string($$functionName)) {
                    return $functions;
                }
                $functions[(string)$functionName] = $$functionName;
                return $functions;
            },
            []
        );
        return $this->importedFunctions;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function functionDefined(string $name): bool
    {
        return (
            function_exists($name)
            || array_key_exists($name, $this->importedFunctions)
            || array_key_exists($name, $GLOBALS)
        );
    }
}
