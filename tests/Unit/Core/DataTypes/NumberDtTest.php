<?php

namespace Core\DataTypes;

use Core\DataTypes\Numbers\NumberDt;
use Tests\TestCase;

/**
 * Class NumberDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group NumberDt
 */
class NumberDtTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created NumberDt
     * When value is or is not provided
     * Then the five members should match default values
     *
     * @test
     */
    public function createdNumberDtInstanceHasCorrectProperties()
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
     *
     * @test
     */
    public function numberAddsCorrectly()
    {
        $number = new NumberDt(2);
        $result = $number->add(2);
        $this->assertEquals(4, $result);
    }
}
