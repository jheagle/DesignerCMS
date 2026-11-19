<?php

namespace Core\Adaptors;

/**
 * The framework's shared static state in one place: loaded config, loaded translations, and every adaptor's cached
 * and custom instances. Static calls such as Logger::info() and Config::get() stay as they are; this is how to start
 * over from nothing, which every test does first (Tests\TestCase::setUp()).
 *
 * @package Core\Adaptors
 */
final class SharedState
{
    /**
     * Forget all of it. The next use of each reads its config and builds its instance again.
     *
     * @return void
     */
    public static function reset(): void
    {
        Config::reset();
        Lang::reset();
        Adaptor::resetAll();
        ExceptionAdaptor::resetAll();
        ErrorAdaptor::resetAll();
    }
}
