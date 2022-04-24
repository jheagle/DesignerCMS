<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class DefaultValueTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group DefaultValue
 */
class DefaultValueTest extends TestCase
{
    /**
     * Given a default and a value
     * When using the defaultValue function on truthy or falsy values
     * Then if the value is truthy then return the value, otherwise return the default.
     *
     * @test
     */
    final public function defaultValueReturnsTheValueOrDefaultIfFalse(): void
    {
        $this->assertTrue(defaultValue('true is truthy', true));
        $this->assertEquals('false is falsy', defaultValue('false is falsy', false));
        $this->assertEquals('null is falsy', defaultValue('null is falsy', null));
        $this->assertEquals('zero is falsy', defaultValue('zero is falsy', 0));
        $this->assertEquals('float zero is falsy', defaultValue('float zero is falsy', 0.0));
        $this->assertEquals('string zero is falsy', defaultValue('string zero is falsy', '0'));
        $this->assertEquals('empty string is falsy', defaultValue('empty string is falsy', ''));
        $this->assertEquals('empty array is falsy', defaultValue('empty array is falsy', []));
    }
}