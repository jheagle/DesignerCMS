<?php

namespace Core\Tests\Unit;

use Core\Tests\TestCase;

/**
 * Class ExampleTest
 *
 * @package Core\Tests\Unit
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
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}
