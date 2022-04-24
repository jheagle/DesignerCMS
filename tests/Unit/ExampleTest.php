<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Class ExampleTest
 *
 * @package Tests\Unit
 *
 * @small
 *
 * @group Unit
 * @group Example
 */
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
    final public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}
