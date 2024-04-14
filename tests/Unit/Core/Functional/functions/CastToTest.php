<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class CastToTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('castTo')]
class CastToTest extends TestCase
{
    /**
     * Given a value
     * When cast to is used
     * Then the specified type will be applied to the value.
     */
    #[Test]
    final public function castToAppliesSpecifiedTypeToTheValue(): void
    {
        $this->assertSame(1, castTo('1', 'int'));
        $this->assertSame(1.0, castTo('1', 'float'));
    }
}