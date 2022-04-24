<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\DataType;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use Tests\TestCase;

/**
 * Class VarCharDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group VarCharDt
 */
class VarCharDtTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created VarCharDt
     * When value is or is not provided
     * Then the four members should match default values
     *
     * @test
     */
    final public function createdVarCharDtInstanceHasCorrectProperties(): void
    {
        $varChar = new VarCharDt();
        $this->assertEquals(16, $varChar->getBits());
        $this->assertEquals(65535, $varChar->getLength());
        $this->assertEquals(65535, $varChar->getMaxLength());
        $this->assertEquals(0, $varChar->getMinLength());
        $this->assertEquals(StringDt::CHARSET_UTF8, $varChar->getCharSet());
        $this->assertEquals(DataType::PRIMITIVE_STRING, $varChar->getPrimitiveType());
        $this->assertEquals('', $varChar->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $varChar->getSystemMaxBits());

        $anotherVarChar = new VarCharDt('hello', ['length' => 255]);
        $this->assertEquals(255, $anotherVarChar->getLength());
        $this->assertEquals('hello', $anotherVarChar->getValue());
    }
}
