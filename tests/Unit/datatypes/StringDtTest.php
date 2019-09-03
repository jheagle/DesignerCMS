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

    /** @test
     * @throws \ReflectionException
     */
    public function stringDtToStringMethodReturnsDescriptionOfClass()
    {
        $string = new StringDt();
        $string->setValue('hello');
        $classDescription = $string->getClassDescription();
        $this->assertStringContainsString('Core\DataTypes\Strings\StringDt {', $classDescription);
        $this->assertStringContainsString('charSet = "UTF-8"', $classDescription);
        $this->assertStringContainsString(
            'public __construct(string value = "", array settings = [])',
            $classDescription
        );
        $this->assertStringContainsString('public getCharSet()', $classDescription);
        $this->assertStringContainsString('public getValue()', $classDescription);
        $this->assertStringContainsString('public setValue(value)', $classDescription);
        $this->assertStringContainsString('Core\DataTypes\DataType', $classDescription);
        $this->assertStringContainsString('value = "hello"', $classDescription);
        $this->assertStringContainsString('primitiveType = "string"', $classDescription);
        $this->assertStringContainsString('systemMaxBits = 64', $classDescription);
        $this->assertStringContainsString('public getSystemMaxBits(): int', $classDescription);
        $this->assertStringContainsString('public getPrimitiveType(): string', $classDescription);
        $this->assertStringContainsString('public isEqual(datatype): bool', $classDescription);
        $this->assertStringContainsString('public getClassDescription(): string', $classDescription);
        $this->assertStringContainsString('final public getClassDescription(): string', $classDescription);
        $this->assertStringContainsString('private getClassMembers(): array', $classDescription);
        $this->assertStringContainsString('private getClassMethods(): array', $classDescription);
        $this->assertStringContainsString('private getMember(memberKey)', $classDescription);
        $this->assertStringContainsString('private setMember(memberKey, value)', $classDescription);
        $this->assertStringContainsString('private applyMemberSettings(array settings = [])', $classDescription);
        $this->assertStringContainsString('}', $classDescription);
    }

    /** @test */
    public function stringDtEqualsToStringOrSelf()
    {
        $string = new StringDt();
        $this->assertTrue($string->isEqual(''));
        $this->assertTrue($string->isEqual($string));
    }
}
