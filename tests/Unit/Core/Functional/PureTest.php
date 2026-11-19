<?php

namespace Tests\Unit\Core\Functional;

use BadFunctionCallException;
use Core\Utilities\Functional\Pure;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
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
    #[RunInSeparateProcess] // a fresh PHP process: nothing has used Pure yet (it only passed after another test had)
    final public function aFunctionMayBeRetrievedFromPure(): void
    {
        $function = Pure::getFunction('defaultValue');

        $this->assertIsCallable($function);
    }

    /**
     * Given a name that is not one of Pure's functions
     * When retrieving it
     * Then the exception names it.
     */
    #[Test]
    #[RunInSeparateProcess]
    final public function anUnknownFunctionIsNamedInTheException(): void
    {
        $this->expectException(BadFunctionCallException::class);
        $this->expectExceptionMessage('No Pure function named notAFunction');
        Pure::getFunction('notAFunction');
    }
}
