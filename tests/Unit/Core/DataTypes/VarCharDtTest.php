<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\DataType;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class VarCharDtTest
 *
 * @package Tests\Unit\Core\DataTypes
 */
#[CoversClass(VarCharDt::class)]
#[Small]
#[Group('Unit')]
#[Group('DataType')]
#[Group('VarCharDt')]
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
     */
    #[Test]
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

    /**
     * Given a VarChar of length 2
     * When it is given a value exactly 2 long, then one 3 long
     * Then the first fits with no warning; only the longer one is truncated (and warned about).
     */
    #[Test]
    final public function onlyLongerValuesAreTruncated(): void
    {
        $warnings = [];
        set_error_handler(function (int $level, string $message) use (&$warnings) {
            $warnings[] = $message;
            return true;
        }, E_USER_WARNING);
        try {
            $exact = new VarCharDt('CA', ['length' => 2]);
            $this->assertSame('CA', $exact->getValue());
            $this->assertSame([], $warnings, 'a value exactly as long fits');
            $long = new VarCharDt('CAN', ['length' => 2]);
            $this->assertSame('CA', $long->getValue());
            $this->assertSame(['Truncating VarChar value: "CAN"'], $warnings);
        } finally {
            restore_error_handler();
        }
    }
}
