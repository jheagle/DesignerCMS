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

    /**
     * Given a newly created DataTypeMock
     * When value is not provided or set
     * Then the three members should match default values
     *
     * @test
     */
    public function createdDataTypeInstanceHasCorrectProperties()
    {
        $dataType = new DataTypeMock();
        $this->assertEquals(PHP_INT_SIZE << 3, $dataType->getSystemMaxBits());
        $this->assertNull($dataType->getValue());
        $this->assertEquals('object', $dataType->getPrimitiveType());
    }

    /**
     * Given a DataTypeMock instance
     * When checking equality
     * Then the isEqual method will return true with the same currently set value value and by the same DataType
     *
     * @test
     */
    public function mockDtEqualsToNullOrSelf()
    {
        $dataType = new DataTypeMock();
        $this->assertTrue($dataType->isEqual(null));
        $this->assertTrue($dataType->isEqual($dataType));

        $someObject = new \stdClass();
        $this->assertFalse($dataType->isEqual($someObject));
    }

    /**
     * Given a DataTypeMock instance
     * When a value is provided with setValue
     * Then the DataType will be isEqual to the newly set value
     *
     * @test
     */
    public function newlySetValueIsReturnedWithGetValue()
    {
        $dataType = new DataTypeMock();
        $this->assertTrue($dataType->isEqual(null));

        $someObject = new \stdClass();
        $dataType->setValue($someObject);
        $this->assertTrue($dataType->isEqual($someObject));
    }
}
