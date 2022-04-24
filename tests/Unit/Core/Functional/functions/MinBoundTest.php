<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class MinBoundTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group minBound
 */
class MinBoundTest extends TestCase
{

    /**
     * Given a min value
     * When applying a number to minBound
     * Then the result can be the original number but not lower than min value
     *
     * @test
     */
    final public function minBoundCanLimitANumber(): void
    {
        $this->assertEquals(1, minBound(1, 1));
        $this->assertEquals(1, minBound(1, -1));
        $this->assertEquals(-1, minBound(-1, -1));
        $this->assertEquals(2, minBound(1, 2));
        $this->assertEquals(1, minBound(1, -2));
        $this->assertEquals(1234567890, minBound(50, 1234567890));
        $this->assertEquals(PHP_INT_MAX, minBound(1, PHP_INT_MAX));
        $this->assertEquals(-1, minBound(-1, PHP_INT_MIN));
        // Test floats
        $this->assertEquals(1.1, minBound(1.1, 1.1));
    }

}