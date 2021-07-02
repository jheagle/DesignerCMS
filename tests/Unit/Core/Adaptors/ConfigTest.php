<?php

namespace Core\Adaptors;

use Tests\mocks\GenericClass;
use Tests\TestCase;

/**
 * Class ConfigTest
 *
 * @package Core\Adaptors
 *
 * @group Unit
 * @group Config
 */
class ConfigTest extends TestCase
{
    /**
     * Reset, clear Config for the tests.
     */
    public function setUp(): void
    {
        parent::setUp();
        Config::reset();
    }

    /**
     * Given some existing config data
     * When calling reset with an empty array
     * Then the new config data will be empty.
     *
     * @test
     */
    public function resetWithEmptyConfigClearsConfigData()
    {
        $this->assertNotEmpty(Config::get());
        Config::reset([]);
        $this->assertEmpty(Config::get());
    }

    /**
     * Given empty config data
     * When setting config data
     * Then the new data will be stored.
     *
     * @test
     */
    public function setConfigDataStoresIt()
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
     *
     * @test
     */
    public function getConfigRetrievesData()
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
     *
     * @test
     */
    public function manageConfigFromExternalClass()
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