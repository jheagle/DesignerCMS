<?php

namespace Core\Utilities\Functional;

/**
 * Trait PureTrait
 *
 * @package Core\Utilities\Functional
 */
trait PureTrait
{
    protected $functionPath = __DIR__ . '/functions/';
    protected $importedFunctions = [];

    /**
     * Modify the path used for retrieving functions to include.
     *
     * @param string $path
     *
     * @return self
     */
    public function setFunctionPath(string $path): self
    {
        $this->functionPath = $path;
        return $this;
    }

    /**
     * Declare all of the functions globally.
     *
     * @return callable[]
     */
    protected function extractFunctions(): array
    {
        return $this->importFunctions(true);
    }

    /**
     * Retrieve an array of all of the functions with the name as the keys.
     *
     * @param bool $declareGlobal
     *
     * @return callable[]
     */
    protected function importFunctions(bool $declareGlobal = false): array
    {
        $this->importedFunctions = array_reduce(
            $this->getNewFunctionFiles(),
            $this->retrieveFunctionDefinition($declareGlobal),
            $this->importedFunctions
        );
        return $this->importedFunctions;
    }

    /**
     * Retrieve an array of all files containing functions which have not yet been included.
     *
     * @return array
     */
    private function getNewFunctionFiles(): array
    {
        return array_filter(scandir($this->functionPath), [$this, 'excludeInvalidFiles']);
    }

    /**
     * Detect if the function associated with the provided file name should be included.
     *
     * @param string $file
     *
     * @return bool
     */
    private function excludeInvalidFiles(string $file): bool
    {
        $functionName = basename("{$this->functionPath}{$file}", '.php');
        return !(preg_match('/^(.){1,2}$/', $file) || $this->functionDefined($functionName));
    }

    /**
     * Return a function which an be used with array_reduce in order to build an array of function definitions.
     *
     * @param bool $declareGlobal
     *
     * @return callable
     */
    private function retrieveFunctionDefinition(bool $declareGlobal): callable
    {
        /**
         * Retrieve the function definition and include the file associated with the function name provided. Append
         * the function onto the provided functions array where the name is the key and the definition is the value.
         *
         * @param array $functions
         * @param string $function
         *
         * @return array
         */
        return function (array $functions, string $function) use ($declareGlobal): array {
            $fileName = "{$this->functionPath}{$function}";
            $functionName = basename($fileName, '.php');
            $$functionName = $GLOBALS[$functionName] ?? $functionName;
            include $fileName;
            if (is_string($$functionName)) {
                return $functions;
            }
            $functions[(string)$functionName] = $$functionName;
            return $functions;
        };
    }

    /**
     * Check if the function already exists to avoid re-import / re-declaration
     *
     * @param string $name
     *
     * @return bool
     */
    private function functionDefined(string $name): bool
    {
        return (
            array_key_exists($name, $this->importedFunctions)
            || array_key_exists($name, $GLOBALS)
        );
    }
}
