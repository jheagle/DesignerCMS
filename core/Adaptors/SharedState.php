<?php

namespace Core\Adaptors;

use Core\Tracking\Tracking;

/**
 * The framework's shared static state in one place: loaded config, loaded translations, every adaptor's cached
 * and custom instances, and the user changes are tracked as (Tracking::actingAs()). Static calls such as
 * Logger::info() and Config::get() stay as they are; this is how to start over from nothing, which every test does
 * first (Tests\TestCase::setUp()).
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
        Tracking::reset();
    }
}
