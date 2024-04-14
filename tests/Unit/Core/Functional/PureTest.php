<?php

namespace Tests\Unit\Core\Functional;

use Core\Utilities\Functional\Pure;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class PureTest
 *
 * @package Tests\Unit\Core\Functional
 */
#[CoversClass(Pure::class)]
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
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
     */
    #[Test]
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
     */
    #[Test]
    final public function aFunctionMayBeRetrievedFromPure(): void
    {
        $function = Pure::getFunction('defaultValue');

        $this->assertIsCallable($function);
    }
}
