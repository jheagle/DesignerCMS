<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class CastToTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group castTo
 */
class CastToTest extends TestCase
{
    /**
     * Given a value
     * When cast to is used
     * Then the specified type will be applied to the value.
     *
     * @test
     */
    final public function castToAppliesSpecifiedTypeToTheValue(): void
    {
        $this->assertSame(1, castTo('1', 'int'));
        $this->assertSame(1.0, castTo('1', 'float'));
    }
}