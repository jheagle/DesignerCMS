<?php

namespace Tests\Unit\Core\Functional;

use Core\Utilities\Functional\Pure;
use Error;
use Tests\TestCase;

/**
 * Class PureTest
 *
 * @package Tests\Unit\Core\Functional
 *
 * @group Unit
 * @group Functional
 * @group Pure
 *
 * @coversDefaultClass Pure
 */
class PureTest extends TestCase
{
    final public function setUp(): void
    {
        // Do not call parent::setUp() to avoid extraction of methods
    }

    /**
     * Given no Pure instance
     * When attempting to instantiate Pure
     * Then an exception should be thrown
     *
     * @return void
     *
     * @test
     */
    final public function itShouldNotBeInstantiable(): void
    {
        $this->expectException(Error::class);

        new Pure();
    }

    /**
     * Given
     * When
     * Then
     *
     * @return void
     *
     * @test
     */
    final public function aFunctionMayBeRetrievedFromPure(): void
    {
        $function = Pure::getFunction('defaultValue');

        $this->assertIsCallable($function);
    }
}
