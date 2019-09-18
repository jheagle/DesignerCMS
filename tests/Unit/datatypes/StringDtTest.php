<?php

namespace Core\Tests\Unit\DataTypes;

use Core\DataTypes\Strings\StringDt;
use Core\Tests\TestCase;

/**
 * Class StringDtTest
 *
 * @package Core\Tests\Unit\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group StringDt
 */
class StringDtTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function createAStringDtInstanceHasCorrectProperties()
    {
        $string = new StringDt();
        $this->assertEquals(StringDt::CHARSET_UTF8, $string->getCharSet());
        $this->assertEquals(StringDt::PRIMITIVE_STRING, $string->getPrimitiveType());
        $this->assertEquals('', $string->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $string->getSystemMaxBits());

        $anotherString = new StringDt('hello');
        $this->assertEquals('hello', $anotherString->getValue());
    }

    /** @test */
    public function stringDtEqualsToStringOrSelf()
    {
        $string = new StringDt();
        $this->assertTrue($string->isEqual(''));
        $this->assertTrue($string->isEqual($string));
    }
}
