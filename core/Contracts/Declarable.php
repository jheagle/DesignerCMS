<?php

namespace Core\Contracts;

interface Declarable
{
    /**
     * Get a string representation of all of this classes member and method declarations.
     *
     * @param bool $includePrivate
     *
     * @return string
     */
    public function getClassDescription(bool $includePrivate = false): string;
}