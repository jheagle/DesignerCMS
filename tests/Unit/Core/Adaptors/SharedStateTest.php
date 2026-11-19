<?php

namespace Tests\Unit\Core\Adaptors;

use Core\Adaptors\Config;
use Core\Adaptors\Lang;
use Core\Adaptors\SharedState;
use Core\Adaptors\Vendor\Logger\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Mocks\GenericClass;
use Tests\TestCase;

/**
 * SharedState::reset() forgets config, translations and adaptor instances, so each test (or anything else that needs
 * to) starts from nothing.
 *
 * @package Tests\Unit\Core\Adaptors
 */
#[CoversClass(SharedState::class)]
#[Group('Unit')]
#[Group('Adaptor')]
class SharedStateTest extends TestCase
{
    /**
     * Given config changed at runtime
     * When resetting
     * Then config is read from its files again.
     */
    #[Test]
    final public function resetRereadsConfig(): void
    {
        Config::set('system.locale', 'fr-ca');
        SharedState::reset();
        $this->assertNull(Config::get('system.locale'));
        $this->assertSame('en-ca', Config::get('system.defaultLocale'));
    }

    /**
     * Given translations loaded from one path
     * When the path changes (in the environment, as config reads it) and state is reset
     * Then the translations are read from the new path.
     */
    #[Test]
    final public function resetRereadsTranslations(): void
    {
        $this->assertSame('this is a test', Lang::get('test.test'));
        $_ENV['LANG_PATH'] = '/nowhere';
        try {
            Config::reset();
            $this->assertSame('this is a test', Lang::get('test.test'), 'still the translations loaded first');
            SharedState::reset();
            $this->assertSame('test', Lang::get('test.test'), 'nothing to load: the key itself');
        } finally {
            unset($_ENV['LANG_PATH']);
        }
    }

    /**
     * Given an adaptor with a stand-in resource
     * When resetting
     * Then the stand-in is gone.
     */
    #[Test]
    final public function resetForgetsAdaptorInstances(): void
    {
        $standIn = new GenericClass(['info' => fn() => null]);
        Logger::setResource($standIn);
        $this->assertSame($standIn, Logger::initialize()->classInstance);
        SharedState::reset();
        $this->assertNotSame($standIn, Logger::initialize()->classInstance);
    }

    /**
     * Given the default config (no LANG_PATH)
     * When reading the translations folder from it
     * Then it is core/lang, which exists (it used to point at core/config/core/lang).
     */
    #[Test]
    final public function defaultLangPathExists(): void
    {
        $saved = $_ENV['LANG_PATH'] ?? null;
        unset($_ENV['LANG_PATH']);
        try {
            SharedState::reset();
            $this->assertDirectoryExists(Config::get('system.langPath'));
            $this->assertSame(
                realpath(dirname(__DIR__, 4) . '/core/lang'),
                realpath(Config::get('system.langPath'))
            );
        } finally {
            if ($saved !== null) {
                $_ENV['LANG_PATH'] = $saved;
            }
        }
    }
}
