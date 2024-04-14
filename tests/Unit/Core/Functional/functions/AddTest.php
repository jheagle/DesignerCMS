<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class AddTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('add')]
class AddTest extends TestCase
{
    /**
     * Given two integers
     * When adding these numbers
     * Then the result is the sum of the two numbers
     */
    #[Test]
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
    }
}