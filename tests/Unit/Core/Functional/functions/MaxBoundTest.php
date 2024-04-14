<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class MaxBoundTest
 *
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('maxBound')]
class MaxBoundTest extends TestCase
{
    /**
     * Given a max value
     * When applying a number to maxBound
     * Then the result can be the original number but not higher than max value.
     */
    #[Test]
    final public function maxBoundCanLimitANumber(): void
    {
        $this->assertEquals(1, maxBound(1, 1));
        $this->assertEquals(-1, maxBound(1, -1));
        $this->assertEquals(-1, maxBound(-1, -1));
        $this->assertEquals(1, maxBound(1, 2));
        $this->assertEquals(-2, maxBound(1, -2));
        $this->assertEquals(50, maxBound(50, 1234567890));
        $this->assertEquals(1, maxBound(1, PHP_INT_MAX));
        $this->assertEquals(PHP_INT_MIN, maxBound(-1, PHP_INT_MIN));
        // Test floats
        $this->assertEquals(1.1, maxBound(1.1, 1.1));
    }
}