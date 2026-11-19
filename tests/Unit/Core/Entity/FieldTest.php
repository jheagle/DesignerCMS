<?php

namespace Tests\Unit\Core\Entity;

use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Field;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Field: a column's name, DataType, default and flags.
 *
 * @package Tests\Unit\Core\Entity
 */
#[CoversClass(Field::class)]
#[Group('Unit')]
#[Group('Entity')]
class FieldTest extends TestCase
{
    /**
     * Given a number type and no length
     * When making the Field
     * Then it works (a null length used to replace the type's default and throw).
     */
    #[Test]
    final public function aNumberFieldNeedsNoLength(): void
    {
        $field = new Field('Quantity Ordered', IntDt::class, 7, null, Field::NOT_NULL | Field::UNSIGNED);
        $this->assertSame('quantity_ordered', $field->getName());
        $this->assertInstanceOf(IntDt::class, $field->getDataType());
        $this->assertSame(7, $field->getDefault());
        $this->assertTrue($field->hasAttr(Field::UNSIGNED));
    }

    /**
     * Given a primary key, and zero fill without unsigned
     * When making the Fields
     * Then the primary key is also required, and zero fill implies unsigned.
     */
    #[Test]
    final public function flagsImplyEachOther(): void
    {
        $this->assertTrue((new Field('id', IntDt::class, 0, null, Field::PRIMARY_KEY))->hasAttr(Field::REQUIRED));
        $this->assertTrue((new Field('n', IntDt::class, 0, 3, Field::ZERO_FILL))->hasAttr(Field::UNSIGNED));
    }

    /**
     * Given a Field read from the database, of a type without a DataType yet
     * When asking about its type
     * Then the exact SQL type is kept and it says it isn't fully supported; a plain Field is supported with no SQL type.
     */
    #[Test]
    final public function sqlTypeAndSupport(): void
    {
        $date = new Field('created', StringDt::class, '', null, Field::NOT_NULL, 'datetime', false);
        $this->assertSame('datetime', $date->getSqlType());
        $this->assertFalse($date->isTypeSupported());
        $plain = new Field('email', VarCharDt::class, '', 80);
        $this->assertNull($plain->getSqlType());
        $this->assertTrue($plain->isTypeSupported());
        $this->assertSame(Field::NOT_NULL, $plain->getAttributes());
    }
}
