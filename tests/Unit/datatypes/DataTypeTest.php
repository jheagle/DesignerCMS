<?php

namespace Core\Tests\Unit\DataTypes;

use Core\Tests\Mocks\DataTypeMock;
use Core\Tests\TestCase;

/**
 * Class DataTypeTest
 *
 * @package Core\Tests\Unit\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group DataTypeMock
 */
class DataTypeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function createdDataTypeInstanceHasCorrectProperties()
    {
        $dataType = new DataTypeMock();
        $this->assertEquals(PHP_INT_SIZE << 3, $dataType->getSystemMaxBits());
        $this->assertNull($dataType->getValue());
    }

    /** @test */
    public function mockDtEqualsToNullOrSelf()
    {
        $dataType = new DataTypeMock();
        $this->assertTrue($dataType->isEqual(null));
        $this->assertTrue($dataType->isEqual($dataType));
    }
}
