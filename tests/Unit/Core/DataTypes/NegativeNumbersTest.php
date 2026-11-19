<?php

namespace Tests\Unit\Core\DataTypes;

use Core\DataTypes\Numbers\Doubles\DecimalDt;
use Core\DataTypes\Numbers\Integers\BigIntDt;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Numbers\Integers\MediumIntDt;
use Core\DataTypes\Numbers\Integers\SmallIntDt;
use Core\DataTypes\Numbers\Integers\TinyIntDt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Negative numbers keep their sign (DESI-40: the integer and decimal types turned -1 into 1).
 *
 * A number keeps its magnitude in value and the sign in isNegative; each constructor layer set the value again from
 * the already-stripped magnitude, so the last one found no '-' and cleared isNegative.
 *
 * @package Tests\Unit\Core\DataTypes
 */
#[Group('Unit')]
#[Group('DataType')]
class NegativeNumbersTest extends TestCase
{
    /**
     * Every integer type.
     *
     * @return array
     */
    public static function integerTypes(): array
    {
        return [
            'TinyIntDt' => [TinyIntDt::class],
            'SmallIntDt' => [SmallIntDt::class],
            'MediumIntDt' => [MediumIntDt::class],
            'IntDt' => [IntDt::class],
            'BigIntDt' => [BigIntDt::class],
        ];
    }

    /**
     * Given an integer type
     * When it is made with a negative number, as an int or as text
     * Then it holds the negative number (and zero and positives are unchanged).
     */
    #[Test]
    #[DataProvider('integerTypes')]
    final public function integersKeepTheirSign(string $type): void
    {
        $this->assertSame(-1, (new $type(-1))->getValue());
        $this->assertSame(-5, (new $type('-5'))->getValue());
        $this->assertTrue((new $type(-1))->getIsNegative());
        $this->assertSame(0, (new $type(0))->getValue());
        $this->assertSame(7, (new $type(7))->getValue());
        $this->assertFalse((new $type(7))->getIsNegative());
    }

    /**
     * Given a value already set
     * When it is changed to a negative one, then back to a positive one
     * Then each change is held as given.
     */
    #[Test]
    #[DataProvider('integerTypes')]
    final public function settingAValueKeepsItsSign(string $type): void
    {
        $number = new $type(3);
        $number->setValue(-9);
        $this->assertSame(-9, $number->getValue());
        $number->setValue(4);
        $this->assertSame(4, $number->getValue());
    }

    /**
     * Given a 19-digit negative number (the most negative 64-bit value but one)
     * When a BigIntDt holds it
     * Then it is exact, as text: numbers this long are kept in parts as a string so they can grow past PHP's int.
     */
    #[Test]
    final public function aLargeNegativeBigInt(): void
    {
        $this->assertSame('-9223372036854775807', (new BigIntDt('-9223372036854775807'))->getValue());
    }

    /**
     * Given a negative whole number
     * When a DecimalDt holds it
     * Then it keeps the sign (fractions are cut on construction: DESI-42).
     */
    #[Test]
    final public function decimalsKeepTheirSign(): void
    {
        $this->assertSame('-3.0', (new DecimalDt(-3))->getValue());
        $this->assertSame('3.0', (new DecimalDt(3))->getValue());
    }
}
