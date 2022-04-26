<?php

namespace Core\Contracts;

use Throwable;

interface ThrowableAdaptor extends Adaptable, Castable
{
    /**
     * ExceptionAdaptor constructor.
     *
     * @param string $message
     * @param mixed ...$args
     */
    public function __construct(string $message, ...$args);

    /**
     * Wrap this class around an existing exception.
     *
     * @param Throwable $throwable
     *
     * @return $this
     */
    public function wrap(Throwable $throwable): self;
}