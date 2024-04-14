<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class CoalesceTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('coalesce')]
class CoalesceTest extends TestCase
{
    /**
     * Given a default or function to compare with
     * When using the coalesce function
     * Then if no values are provided it will be null or the default, otherwise the first value to not match.
     */
    #[Test]
    final public function coalesceReturnsFirstNotMatchingOrDefault(): void
    {
        $this->assertNull(coalesce());
        $this->assertNull(
            coalesce(function ($value) {
                return $value !== null;
            })
        );
        $this->assertEquals(3, coalesce(3));
        $this->assertNull(
            coalesce(function ($value) {
                return $value !== 3;
            })
        );
        $this->assertEquals(
            'something',
            coalesce(function ($value) {
                return !!$value;
            }, 0, false, null, '', '0', [], 0.0, 'something')
        );
    }
}