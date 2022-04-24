<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class EnvGetTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group envGet
 */
class EnvGetTest extends TestCase
{
    /**
     * Given some environment variables
     * When calling envGet with the env path
     * Then the environment variables should be returned.
     *
     *
     * @test
     */
    final public function envGetRetrievesValuesFromEnvGlobal(): void
    {
        $_ENV['TEST_ENV_GET_TEST'] = 'test';
        $_ENV['TEST_ENV_GET_TEST_2']['details'] = 'test2';
        $this->assertEquals('test', envGet('TEST_ENV_GET_TEST'));
        $this->assertEquals('test2', envGet('TEST_ENV_GET_TEST_2.details'));
    }
}