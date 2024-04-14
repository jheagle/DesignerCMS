<?php

namespace Tests\Unit\Core\Adaptors;

use Core\Adaptors\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Mocks\GenericClass;
use Tests\TestCase;

/**
 * Class ConfigTest
 *
 * @package Tests\Unit\Core\Adaptors
 */
#[Group('Unit')]
#[Group('Config')]
class ConfigTest extends TestCase
{
    /**
     * Reset, clear Config for the tests.
     */
    final public function setUp(): void
    {
        parent::setUp();
        Config::reset();
    }

    /**
     * Given some existing config data
     * When calling reset with an empty array
     * Then the new config data will be empty.
     */
    #[Test]
    final public function resetWithEmptyConfigClearsConfigData(): void
    {
        $this->assertNotEmpty(Config::get());
        Config::reset([]);
        $this->assertEmpty(Config::get());
    }

    /**
     * Given empty config data
     * When setting config data
     * Then the new data will be stored.
     */
    #[Test]
    final public function setConfigDataStoresIt(): void
    {
        Config::reset([]);
        Config::set('adapters.something', ['this thing']);
        $this->assertEquals(
            [
                'adapters' => [
                    'something' => ['this thing'],
                ],
            ],
            Config::get()
        );
    }

    /**
     * Given existing config data
     * When calling get on the config data with dot-notation
     * Then expected value will be returned.
     */
    #[Test]
    final public function getConfigRetrievesData(): void
    {
        Config::reset(
            [
                'adapters' => [
                    'something' => 'this thing',
                ],
            ]
        );
        $this->assertEquals(
            'this thing',
            Config::get('adapters.something')
        );
    }

    /**
     * Given an external class using Config
     * When we set the config here and call it in the external class
     * Then the same config data will be used.
     */
    #[Test]
    final public function manageConfigFromExternalClass(): void
    {
        Config::reset(
            [
                'adapters' => [
                    'something' => 'this thing',
                ],
            ]
        );
        $mockClass = new GenericClass(
            [
                'getConfig' => function (string $dotNotation) {
                    return Config::get($dotNotation);
                },
            ]
        );
        $this->assertEquals(
            'this thing',
            $mockClass->getConfig('adapters.something')
        );
    }
}