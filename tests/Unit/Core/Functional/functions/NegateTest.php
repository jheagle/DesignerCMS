<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class NegateTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('negate')]
class NegateTest extends TestCase
{
    /**
     * Given an integer
     * When applying the integer to negate
     * Then the result will be the same absolute value but with the signed bit switched
     */
    #[Test]
    final public function negateWillToggleTheSignedBit(): void
    {
        $this->assertEquals(-1, negate(1));
        $this->assertEquals(1, negate(-1));
        $this->assertEquals(-2, negate(2));
        $this->assertEquals(2, negate(-2));
        $this->assertEquals(PHP_INT_MIN + 1, negate(PHP_INT_MAX));
        // Absolute value of PHP_INT_MIN is greater than PHP_INT_MAX (by 1), so negating wraps back to negative
        $this->assertEquals(PHP_INT_MIN, negate(PHP_INT_MIN));
    }
}