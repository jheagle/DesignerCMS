<?php

namespace Tests\Unit\Core\Adaptors;

use Core\Adaptors\Config;
use Core\Adaptors\Lang;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class LangTest
 *
 * @package Tests\Unit\Core\Adaptors
 */
#[CoversClass(Lang::class)]
#[Group('Unit')]
#[Group('Lang')]
class LangTest extends TestCase
{
    /**
     * Given any missing translation
     * When there is no translation or a default locale translation
     * Then either the last key will be returned otherwise the default local translation if available.
     */
    #[Test]
    final public function getMissingTranslationsReturnsLastKeyAsTranslation(): void
    {
        $this->assertEquals(
            'missing',
            Lang::get('test.missing')
        );
        Config::set('system.locale', 'zz-zz');
        $this->assertEquals(
            'missing',
            Lang::get('test.missing')
        );
        $this->assertEquals(
            'English translation',
            Lang::get('test.englishOnly')
        );
    }

    /**
     * Given defaultLocale of en-ca
     * When there are matching translations
     * Then the correct text will be returned.
     */
    #[Test]
    final public function getCorrectEnglishTranslations(): void
    {
        Config::reset();
        $this->assertEquals(
            'this is a test',
            Lang::get('test.test')
        );
        $this->assertEquals(
            'testing; one, 2, 3.00',
            Lang::get('test.testWithValues', [Lang::get('test.one'), 2, 3])
        );
    }

    /**
     * Given the set locale of fr-ca
     * When there are matching translations
     * Then the correct text will be returned.
     */
    #[Test]
    final public function getCorrectFrenchTranslations(): void
    {
        Config::set('system.locale', 'fr-ca');
        $this->assertEquals(
            "c'est un test",
            Lang::get('test.test')
        );
        $this->assertEquals(
            'tester; un, 2, 3.00',
            Lang::get('test.testWithValues', [Lang::get('test.one'), 2, 3])
        );
    }

    /**
     * Given the set locale of es-mx
     * When there are matching translations
     * Then the correct text will be returned.
     */
    #[Test]
    final public function getCorrectSpanishTranslations(): void
    {
        Config::set('system.locale', 'es-mx');
        $this->assertEquals(
            'eso es una prueba',
            Lang::get('test.test')
        );
        $this->assertEquals(
            'probando; uno, 2, 3.00',
            Lang::get('test.testWithValues', [Lang::get('test.one'), 2, 3])
        );
    }
}