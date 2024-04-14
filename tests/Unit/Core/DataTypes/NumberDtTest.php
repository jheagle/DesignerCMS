<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\Numbers\NumberDt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class NumberDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 */
#[CoversClass(NumberDt::class)]
#[Small]
#[Group('Unit')]
#[Group('DataType')]
#[Group('NumberDt')]
class NumberDtTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created NumberDt
     * When value is or is not provided
     * Then the five members should match default values
     */
    #[Test]
    final public function createdNumberDtInstanceHasCorrectProperties(): void
    {
        $number = new NumberDt();
        $this->assertEquals('/[^\d.]/', $number->getFilter());
        $this->assertFalse($number->getIsNegative());
        $this->assertTrue($number->getIsSigned());
        $this->assertEquals(0, $number->getLength());
        $this->assertEquals([0], $number->getValueSplit());

        $anotherNumber = new NumberDt('100');
        $this->assertEquals(100, $anotherNumber->getValue());
    }

    /**
     * Given a newly created NumberDt
     * When value is or is not provided
     * Then the five members should match default values
     */
    #[Test]
    final public function numberAddsCorrectly(): void
    {
        $number = new NumberDt(2);
        $result = $number->add(2);
        $this->assertEquals(4, $result);
    }
}
