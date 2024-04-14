<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\DataType;
use Core\DataTypes\Strings\StringDt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class StringDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 */
#[CoversClass(StringDt::class)]
#[Small]
#[Group('Unit')]
#[Group('DataType')]
#[Group('StringDt')]
class StringDtTest extends TestCase
{
    final public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a newly created StringDt
     * When value is or is not provided
     * Then the four members should match default values
     */
    #[Test]
    final public function createdStringDtInstanceHasCorrectProperties(): void
    {
        $string = new StringDt();
        $this->assertEquals(StringDt::CHARSET_UTF8, $string->getCharSet());
        $this->assertEquals(DataType::PRIMITIVE_STRING, $string->getPrimitiveType());
        $this->assertEquals('', $string->getValue());
        $this->assertEquals(PHP_INT_SIZE << 3, $string->getSystemMaxBits());

        $anotherString = new StringDt('hello');
        $this->assertEquals('hello', $anotherString->getValue());
    }

    /**
     * Given a StringDt instance
     * When checking equality
     * Then the isEqual method will return true for a string matching the set value and for the same StringDt instance
     */
    #[Test]
    final public function stringDtEqualsToStringOrSelf(): void
    {
        $string = new StringDt();
        $this->assertTrue($string->isEqual(''));
        $this->assertTrue($string->isEqual($string));
    }
}
