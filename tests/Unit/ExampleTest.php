<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Tests\TestCase;

/**
 * Class ExampleTest
 *
 * @package Tests\Unit
 */
#[Small]
#[Group('Unit')]
#[Group('Example')]
class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * Given the boolean value true
     * When asserting the boolean is true
     * Then assertion will pass for true
     *
     * @return void
     */
    final public function testBasicTest(): void
    {
        $this->assertTrue(true);
    }
}
