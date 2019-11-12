<?php

namespace Core\Tests\Unit\DataTypes;

use Core\DataTypes\Strings\CharDt;
use Core\Tests\TestCase;

/**
 * Class CharDtTest
 *
 * @package Core\Tests\Unit\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group CharDt
 */
class CharDtTest extends TestCase
{
    public function setUp()
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
    public function createdCharDtInstanceHasCorrectProperties()
    {
        $char = new CharDt();
        $this->assertEquals(8, $char->getBits());
        $this->assertEquals(1, $char->getLength());
        $this->assertEquals(255, $char->getMaxLength());
        $this->assertEquals(0, $char->getMinLength());
        $this->assertEquals(CharDt::CHARSET_UTF8, $char->getCharSet());
        $this->assertEquals(CharDt::PRIMITIVE_STRING, $char->getPrimitiveType());
        $this->assertEquals('', $char->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $char->getSystemMaxBits());

        $anotherChar = new CharDt('hello', ['length' => 15]);
        $this->assertEquals(15, $anotherChar->getLength());
        $this->assertEquals('hello', $anotherChar->getValue());
    }
}
