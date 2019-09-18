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
    public function createADataTypeInstanceHasCorrectProperties()
    {
        $dataType = new DataTypeMock('');
        $this->assertEquals('', $dataType->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $dataType->getSystemMaxBits());

        $anotherString = new DataTypeMock('hello');
        $this->assertEquals('hello', $anotherString->getValue());
    }

    /** @test */
    public function stringDtEqualsToStringOrSelf()
    {
        $dataType = new DataTypeMock('');
        $this->assertTrue($dataType->isEqual(''));
        $this->assertTrue($dataType->isEqual($dataType));
    }
}
