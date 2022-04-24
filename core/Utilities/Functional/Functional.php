<?php

namespace Core\Utilities\Functional;

interface Functional
{
    /**
     * Modify the path used for retrieving functions to include.
     *
     * @param string $path
     *
     * @return Functional
     */
    public function setFunctionPath(string $path): self;

    /**
     * Declare all of the functions globally.
     *
     * @return callable[]
     */
    public function extractFunctions(): array;

    /**
     * Retrieve an array of all of the functions with the name as the keys.
     *
     * @param bool $declareGlobal
     *
     * @return callable[]
     */
    public function importFunctions(bool $declareGlobal = false): array;
}