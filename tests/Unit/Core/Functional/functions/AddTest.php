<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class AddTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group add
 */
class AddTest extends TestCase
{
    /**
     * Given two integers
     * When adding these numbers
     * Then the result is the sum of the two numbers
     *
     * @test
     */
    final public function addCanMakeATotal(): void
    {
        $this->assertEquals(2, add(1, 1));
        $this->assertEquals(0, add(1, -1));
        $this->assertEquals(-2, add(-1, -1));
        $this->assertEquals(3, add(1, 2));
        $this->assertEquals(-1, add(1, -2));
        $this->assertEquals(1234567940, add(50, 1234567890));
        // Test overflow
        $this->assertEquals(PHP_INT_MIN, add(1, PHP_INT_MAX));
        $this->assertEquals(PHP_INT_MAX, add(-1, PHP_INT_MIN));
        // This add function works on int not floats
        $this->assertEquals(2, add(1.1, 1.1));
    }
}