<?php

namespace Tests\Unit\Core\Entity;

use Core\Database\PhpDbConnect;
use Core\DataTypes\Numbers\Doubles\DecimalDt;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Numbers\Integers\SmallIntDt;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Entity;
use Core\Entity\Field;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\UsesTestDatabase;

/**
 * A small entity for the tests: what a generated model looks like.
 */
class EntityTestOrder extends Entity
{
    public const TABLE = 'entity_test_orders';

    /** @var string[] The hooks called, in order. */
    public array $hooks = [];

    protected static function fieldList(): array
    {
        return [
            new Field('id', IntDt::class, null, null, Field::PRIMARY_KEY | Field::AUTO_INCREMENT | Field::UNSIGNED | Field::NOT_NULL),
            new Field('customer', VarCharDt::class, null, 50, Field::NOT_NULL | Field::UNIQUE),
            new Field('total', DecimalDt::class, '0', null, Field::NOT_NULL),
            new Field('note', VarCharDt::class, null, 100, 0),
            new Field('quantity', SmallIntDt::class, 1, null, Field::NOT_NULL | Field::UNSIGNED),
            new Field('created', StringDt::class, null, null, Field::NOT_NULL, 'datetime', false),
        ];
    }

    protected function afterCreate(): void
    {
        $this->hooks[] = 'created';
    }

    protected function afterUpdate(): void
    {
        $this->hooks[] = 'updated';
    }

    protected function afterDelete(): void
    {
        $this->hooks[] = 'deleted';
    }
}

/**
 * An entity whose key the caller supplies (a code, not auto-increment).
 */
class EntityTestCountry extends Entity
{
    public const TABLE = 'entity_test_countries';

    /** @var string[] */
    public array $hooks = [];

    protected static function fieldList(): array
    {
        return [
            new Field('code', VarCharDt::class, null, 2, Field::PRIMARY_KEY | Field::NOT_NULL),
            new Field('name', VarCharDt::class, null, 50, Field::NOT_NULL),
        ];
    }

    protected function afterCreate(): void
    {
        $this->hooks[] = 'created';
    }

    protected function afterUpdate(): void
    {
        $this->hooks[] = 'updated';
    }
}

/**
 * An entity with no TABLE and no primary key, for the naming and key rules.
 */
class LineItemNote extends Entity
{
    protected static function fieldList(): array
    {
        return [new Field('text', VarCharDt::class, '', 20)];
    }
}

/**
 * The generic Entity against the MySQL test database.
 *
 * @package Tests\Unit\Core\Entity
 */
#[CoversClass(Entity::class)]
#[Group('Unit')]
#[Group('Database')]
class EntityTest extends TestCase
{
    use UsesTestDatabase;

    private PhpDbConnect $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->connectTestDatabase();
        $this->db->alter('CREATE TEMPORARY TABLE entity_test_orders (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            customer VARCHAR(50) NOT NULL UNIQUE,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            note VARCHAR(100) NULL,
            quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
        $this->db->alter('CREATE TEMPORARY TABLE entity_test_countries (code CHAR(2) NOT NULL PRIMARY KEY, name VARCHAR(50) NOT NULL)');
    }

    public function tearDown(): void
    {
        $this->disconnectTestDatabase();
        parent::tearDown();
    }

    private function order(array $values): EntityTestOrder
    {
        return new EntityTestOrder($this->db, $values);
    }

    /**
     * Given a new order
     * When it is saved and found again
     * Then the id is filled in, the values come back (integers as int, NULL as null, decimals as text), and the
     * database's own default for the column left out is in the entity right after the save.
     */
    #[Test]
    final public function saveInsertsAndFindReadsBack(): void
    {
        $order = $this->order(['customer' => 'Ada', 'total' => '9.50'])->save();
        $this->assertSame(1, $order->id());
        $this->assertNotNull($order->created, 'read back after the insert: the database filled it');
        $found = EntityTestOrder::find($this->db, 1);
        $this->assertSame('Ada', $found->customer);
        $this->assertSame('9.50', $found->total);
        $this->assertSame(1, $found->quantity);
        $this->assertNull($found->note);
        $this->assertNotNull($found->created, 'CURRENT_TIMESTAMP applied by the database');
        $this->assertNull(EntityTestOrder::find($this->db, 99));
    }

    /**
     * Given a saved order
     * When a value changes and it is saved again
     * Then that row is updated (not a new one), and a cleared value becomes NULL.
     */
    #[Test]
    final public function saveUpdatesAnExistingRow(): void
    {
        $order = $this->order(['customer' => 'Ada', 'note' => 'gift'])->save();
        $this->order(['customer' => 'Grace'])->save();
        $order->quantity = 3;
        $order->note = null;
        $order->save();
        $found = EntityTestOrder::find($this->db, $order->id());
        $this->assertSame(3, $found->quantity);
        $this->assertNull($found->note);
        $this->assertSame(1, EntityTestOrder::find($this->db, 2)->quantity, 'the other row is untouched');
        $this->assertSame(2, (int)$this->db->fetchValue('SELECT COUNT(*) FROM entity_test_orders'));
    }

    /**
     * Given a saved order
     * When it is deleted, and deleted again
     * Then the first deletes the row and the second reports nothing deleted; an unsaved one deletes nothing.
     */
    #[Test]
    final public function deleteRemovesTheRow(): void
    {
        $order = $this->order(['customer' => 'Ada'])->save();
        $this->assertTrue($order->delete());
        $this->assertNull(EntityTestOrder::find($this->db, $order->id()));
        $this->assertFalse($order->delete());
        $this->assertFalse($this->order(['customer' => 'never saved'])->delete());
    }

    /**
     * Given several orders
     * When finding by values, by NULL, and in order
     * Then the matching rows come back as entities.
     */
    #[Test]
    final public function findWhereMatchesAndOrders(): void
    {
        $this->order(['customer' => 'Ada', 'quantity' => 2])->save();
        $this->order(['customer' => 'Grace', 'quantity' => 2, 'note' => 'rush'])->save();
        $this->order(['customer' => 'Linus', 'quantity' => 5])->save();
        $names = fn(array $orders) => array_map(fn(EntityTestOrder $o) => $o->customer, $orders);
        $this->assertSame(['Ada', 'Grace'], $names(EntityTestOrder::findWhere($this->db, ['quantity' => 2], 'customer')));
        $this->assertSame(['Linus', 'Ada'], $names(EntityTestOrder::findWhere($this->db, ['note' => null], 'quantity', true)));
        $this->assertCount(3, EntityTestOrder::findWhere($this->db));
    }

    /**
     * Given values that break their Fields' rules
     * When saving
     * Then nothing is written and every problem is named at once.
     */
    #[Test]
    final public function validationNamesEveryProblem(): void
    {
        try {
            $this->order(['customer' => null, 'quantity' => -2])->save();
            $this->fail('Saving should have been refused');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('customer needs a value', $e->getMessage());
            $this->assertStringContainsString("quantity can't be negative", $e->getMessage());
        }
        $this->assertSame(0, (int)$this->db->fetchValue('SELECT COUNT(*) FROM entity_test_orders'));
    }

    /**
     * Given a name that isn't a column
     * When getting, setting or finding by it
     * Then it is a clear error (and never reaches the SQL).
     */
    #[Test]
    final public function unknownColumnsAreErrors(): void
    {
        $order = $this->order(['customer' => 'Ada']);
        foreach ([
            fn() => $order->get('nope'),
            fn() => $order->set('nope', 1),
            fn() => new EntityTestOrder($this->db, ['nope' => 1]),
            fn() => EntityTestOrder::findWhere($this->db, ['id = 1 OR 1' => 1]),
            fn() => EntityTestOrder::findWhere($this->db, [], 'customer; DROP TABLE x'),
        ] as $call) {
            try {
                $call();
                $this->fail('Should have been refused');
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('has no column named', $e->getMessage());
            }
        }
    }

    /**
     * Given a value that would break SQL pasted into it
     * When it is saved and searched for
     * Then it is stored and found exactly, and the table is still there.
     */
    #[Test]
    final public function valuesAreBound(): void
    {
        $hostile = "Ada'); DROP TABLE entity_test_orders; --";
        $this->order(['customer' => $hostile])->save();
        $this->assertSame($hostile, EntityTestOrder::findWhere($this->db, ['customer' => $hostile])[0]->customer);
    }

    /**
     * Given an order going through its life
     * When it is created, updated and deleted
     * Then each hook runs once, in order (tracking hooks in here).
     */
    #[Test]
    final public function hooksRunAfterEachWrite(): void
    {
        $order = $this->order(['customer' => 'Ada'])->save();
        $order->quantity = 2;
        $order->save();
        $order->delete();
        $this->assertSame(['created', 'updated', 'deleted'], $order->hooks);
    }

    /**
     * Given entities with and without a TABLE, and without a primary key
     * When asking for the table and key
     * Then the table defaults to the class name in snake case, and a missing key is a clear error.
     */
    #[Test]
    final public function tableNamesAndKeys(): void
    {
        $this->assertSame('entity_test_orders', EntityTestOrder::table());
        $this->assertSame('line_item_note', LineItemNote::table());
        $this->assertSame('id', EntityTestOrder::primaryKey()->getName());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('has no primary key Field');
        LineItemNote::primaryKey();
    }

    /**
     * Given two loaded copies of the same row and another row
     * When comparing
     * Then the same row compares equal, and rows order by id.
     */
    #[Test]
    final public function compareByPrimaryKey(): void
    {
        $this->order(['customer' => 'Ada'])->save();
        $this->order(['customer' => 'Grace'])->save();
        $first = EntityTestOrder::find($this->db, 1);
        $this->assertSame(0, $first->compareTo(EntityTestOrder::find($this->db, 1)));
        $this->assertSame(-1, $first->compareTo(EntityTestOrder::find($this->db, 2)));
    }

    /**
     * Given an entity whose key the caller supplies
     * When it is saved, changed, re-keyed and found
     * Then the first save inserts (it used to run an UPDATE that matched nothing), later saves update that row.
     */
    #[Test]
    final public function aCallerSuppliedKeyInsertsThenUpdates(): void
    {
        $country = (new EntityTestCountry($this->db, ['code' => 'CA', 'name' => 'Canada']))->save();
        $this->assertSame('Canada', EntityTestCountry::find($this->db, 'CA')->name);
        $country->name = 'Canada (CA)';
        $country->save();
        $country->code = 'CN';
        $country->save();
        $this->assertNull(EntityTestCountry::find($this->db, 'CA'), 're-keyed: the same row, under its new key');
        $this->assertSame('Canada (CA)', EntityTestCountry::find($this->db, 'CN')->name);
        $this->assertSame(['created', 'updated', 'updated'], $country->hooks);
        $this->assertSame(1, (int)$this->db->fetchValue('SELECT COUNT(*) FROM entity_test_countries'));
    }

    /**
     * Given an explicit id for an auto-increment key
     * When saving a new order with it
     * Then it is inserted with that id.
     */
    #[Test]
    final public function anExplicitIdIsInserted(): void
    {
        $this->order(['id' => 42, 'customer' => 'Ada'])->save();
        $this->assertSame('Ada', EntityTestOrder::find($this->db, 42)->customer);
    }

    /**
     * Given a loaded order saved again with nothing changed
     * When saving
     * Then nothing is written and afterUpdate doesn't run (there was no change to record).
     */
    #[Test]
    final public function anUnchangedSaveRunsNoHook(): void
    {
        $this->order(['customer' => 'Ada'])->save();
        $order = EntityTestOrder::find($this->db, 1);
        $order->save();
        $this->assertSame([], $order->hooks);
    }

    /**
     * Given a loaded order whose row is deleted by someone else
     * When saving it
     * Then it says the row no longer exists (and no hook runs).
     */
    #[Test]
    final public function updatingAMissingRowIsAnError(): void
    {
        $this->order(['customer' => 'Ada'])->save();
        $order = EntityTestOrder::find($this->db, 1);
        $this->db->delete('DELETE FROM entity_test_orders WHERE id = ?', [1]);
        $order->quantity = 5;
        try {
            $order->save();
            $this->fail('The save should have been refused');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('no longer exists', $e->getMessage());
        }
        $this->assertSame([], $order->hooks);
    }

    /**
     * Given a deleted order
     * When it is saved again
     * Then it is inserted again.
     */
    #[Test]
    final public function savingADeletedRowInsertsItAgain(): void
    {
        $order = $this->order(['customer' => 'Ada'])->save();
        $order->delete();
        $order->save();
        $this->assertSame('Ada', EntityTestOrder::find($this->db, $order->id())->customer);
    }

    /**
     * Given a saved order changed in the database behind it
     * When refreshed
     * Then it holds the database's values; toArray and isset reflect them. A row never saved can't be refreshed.
     */
    #[Test]
    final public function refreshToArrayAndIsset(): void
    {
        $order = $this->order(['customer' => 'Ada'])->save();
        $this->db->update('UPDATE entity_test_orders SET note = ? WHERE id = ?', ['changed', 1]);
        $this->assertFalse(isset($order->note));
        $order->refresh();
        $this->assertSame('changed', $order->toArray()['note']);
        $this->assertTrue(isset($order->note));
        $this->expectException(\LogicException::class);
        $this->order(['customer' => 'never saved'])->refresh();
    }

    /**
     * Given things that aren't an entity of the same class
     * When comparing
     * Then it never crashes, and the comparison is the same both ways round.
     */
    #[Test]
    final public function compareToCopesWithAnything(): void
    {
        $order = $this->order(['customer' => 'Ada']);
        $country = new EntityTestCountry($this->db, ['code' => 'CA', 'name' => 'Canada']);
        $this->assertSame(1, $order->compareTo(null));
        $this->assertSame(1, $order->compareTo('text'));
        $this->assertSame(-$order->compareTo($country), $country->compareTo($order));
    }
}
