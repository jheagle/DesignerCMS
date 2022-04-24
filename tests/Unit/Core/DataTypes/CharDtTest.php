<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\DataType;
use Core\DataTypes\Strings\CharDt;
use Core\DataTypes\Strings\StringDt;
use Tests\TestCase;

/**
 * Class CharDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group CharDt
 */
class CharDtTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created CharDt
     * When value is or is not provided
     * Then the four members should match default values
     *
     * @test
     */
    final public function createdCharDtInstanceHasCorrectProperties(): void
    {
        $char = new CharDt();
        $this->assertEquals(8, $char->getBits());
        $this->assertEquals(1, $char->getLength());
        $this->assertEquals(255, $char->getMaxLength());
        $this->assertEquals(0, $char->getMinLength());
        $this->assertEquals(StringDt::CHARSET_UTF8, $char->getCharSet());
        $this->assertEquals(DataType::PRIMITIVE_STRING, $char->getPrimitiveType());
        $this->assertEquals('', $char->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $char->getSystemMaxBits());

        $anotherChar = new CharDt('hello', ['length' => 15]);
        $this->assertEquals(15, $anotherChar->getLength());
        $this->assertEquals('hello', $anotherChar->getValue());
    }
}
