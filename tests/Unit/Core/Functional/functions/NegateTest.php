<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class NegateTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group negate
 */
class NegateTest extends TestCase
{
    /**
     * Given an integer
     * When applying the integer to negate
     * Then the result will be the same absolute value but with the signed bit switched
     *
     * @test
     */
    final public function negateWillToggleTheSignedBit(): void
    {
        $this->assertEquals(-1, negate(1));
        $this->assertEquals(1, negate(-1));
        $this->assertEquals(-2, negate(2));
        $this->assertEquals(2, negate(-2));
        $this->assertEquals(PHP_INT_MIN + 1, negate(PHP_INT_MAX));
        // Absolute value of PHP_INT_MIN is greater than PHP_INT_MAX (by 1), so negating wraps back to negative
        $this->assertEquals(PHP_INT_MIN, negate(PHP_INT_MIN));
        // This negate function works on int not floats (leverages add)
        $this->assertEquals(-1, negate(1.1));
    }
}