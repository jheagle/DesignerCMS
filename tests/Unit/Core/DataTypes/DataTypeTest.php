<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\DataType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\Mocks\DataTypeMock;
use Tests\TestCase;

/**
 * Class DataTypeTest
 *
 * @package Tests\Unit\Core\DataTypes
 */
#[CoversClass(DataType::class)]
#[Small]
#[Group('Unit')]
#[Group('DataType')]
#[Group('DataTypeMock')]
class DataTypeTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created DataTypeMock
     * When value is not provided or set
     * Then the three members should match default values
     */
    #[Test]
    final public function createdDataTypeInstanceHasCorrectProperties(): void
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
     */
    #[Test]
    final public function mockDtEqualsToNullOrSelf(): void
    {
        $dataType = new DataTypeMock();
        $this->assertTrue($dataType->isEqual(null));
        $this->assertTrue($dataType->isEqual($dataType));

        $someObject = new stdClass();
        $this->assertFalse($dataType->isEqual($someObject));
    }

    /**
     * Given a DataTypeMock instance
     * When a value is provided with setValue
     * Then the DataType will be isEqual to the newly set value
     */
    #[Test]
    final public function newlySetValueIsReturnedWithGetValue(): void
    {
        $dataType = new DataTypeMock();
        $this->assertTrue($dataType->isEqual(null));

        $someObject = new stdClass();
        $dataType->setValue($someObject);
        $this->assertTrue($dataType->isEqual($someObject));
    }
}
