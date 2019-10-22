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

    /**
     * Given a newly created StringDt
     * When value is or is not provided
     * Then the four members should match default values
     *
     * @test
     */
    public function createdStringDtInstanceHasCorrectProperties()
    {
        $string = new StringDt();
        $this->assertEquals(StringDt::CHARSET_UTF8, $string->getCharSet());
        $this->assertEquals(StringDt::PRIMITIVE_STRING, $string->getPrimitiveType());
        $this->assertEquals('', $string->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $string->getSystemMaxBits());

        $anotherString = new StringDt('hello');
        $this->assertEquals('hello', $anotherString->getValue());
    }

    /**
     * Given a StringDt instance
     * When checking equality
     * Then the isEqual method will return true for a string matching the set value and for the same StringDt instance
     *
     * @test
     */
    public function stringDtEqualsToStringOrSelf()
    {
        $string = new StringDt();
        $this->assertTrue($string->isEqual(''));
        $this->assertTrue($string->isEqual($string));
    }
}
