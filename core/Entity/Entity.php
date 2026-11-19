<?php

namespace Core\Entity;

use Core\Database\DbConnect;
use Core\DataTypes\Numbers\Integers\BigIntDt;
use Core\Entity\Interfaces\EntityObject;
use InvalidArgumentException;
use LogicException;
use Throwable;

/**
 * One row of a table, built from the table's Fields. A subclass only declares what the table is:
 *
 *     class Order extends Entity
 *     {
 *         public const TABLE = 'orders';
 *
 *         protected static function fieldList(): array
 *         {
 *             return [
 *                 new Field('id', IntDt::class, null, null, Field::PRIMARY_KEY | Field::AUTO_INCREMENT | Field::UNSIGNED),
 *                 new Field('total', DecimalDt::class, 0),
 *             ];
 *         }
 *     }
 *
 * and gets reading and writing from that: Order::find($db, 7), Order::findWhere($db, ['paid' => 1]),
 * (new Order($db, ['total' => 9.5]))->save(), $order->delete(). Every query is a prepared statement: values are bound,
 * and column names are only ever the declared Fields' names. (This is the shape the model generator writes.)
 *
 * @package Core\Entity
 */
abstract class Entity implements EntityObject
{
    /**
     * The table name; empty means the class name in snake case (Order -> order).
     */
    public const TABLE = '';

    /**
     * The changes onChange() is told about.
     */
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DELETED = 'deleted';

    /**
     * The Fields of each Entity class, built once per class.
     *
     * @var array<string, array<string, Field>>
     */
    private static array $fieldsByClass = [];

    /**
     * This row's values, keyed by column name. A column not set yet holds its Field's default.
     *
     * @var array<string, mixed>
     */
    protected array $values = [];

    /**
     * The primary key this row has in the database, or null while it isn't there (new, or deleted). Updates and
     * deletes find the row by it.
     */
    protected mixed $persistedKey = null;

    /**
     * The values as the database last had them (read or saved), to tell which columns changed. Empty while the row
     * isn't in the database.
     *
     * @var array<string, mixed>
     */
    protected array $persistedValues = [];

    /**
     * @param DbConnect $db The connection this row is read from and written to.
     * @param array<string, mixed> $values Column values to start with (unknown names are an error).
     */
    public function __construct(protected readonly DbConnect $db, array $values = [])
    {
        foreach (static::fields() as $name => $field) {
            $this->values[$name] = $field->getDefault();
        }
        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * The table's columns as Fields. Declared by the subclass (or the generated base class).
     *
     * @return Field[]
     */
    abstract protected static function fieldList(): array;

    /**
     * The table this entity's rows live in.
     *
     * @return string
     */
    public static function table(): string
    {
        if (static::TABLE !== '') {
            return static::TABLE;
        }
        $shortName = substr(strrchr('\\' . static::class, '\\'), 1);

        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $shortName)), '_');
    }

    /**
     * The columns, keyed by name, in declared order.
     *
     * @return array<string, Field>
     */
    public static function fields(): array
    {
        if (!isset(self::$fieldsByClass[static::class])) {
            $fields = [];
            foreach (static::fieldList() as $field) {
                $fields[$field->getName()] = $field;
            }
            self::$fieldsByClass[static::class] = $fields;
        }

        return self::$fieldsByClass[static::class];
    }

    /**
     * The primary key's Field (one column; composite keys aren't supported yet).
     *
     * @return Field
     *
     * @throws InvalidArgumentException When no Field is the primary key.
     */
    public static function primaryKey(): Field
    {
        foreach (static::fields() as $field) {
            if ($field->hasAttr(Field::PRIMARY_KEY)) {
                return $field;
            }
        }
        throw new InvalidArgumentException(static::class . ' has no primary key Field');
    }

    /**
     * One row by its primary key, or null when there is none.
     *
     * @param DbConnect $db
     * @param mixed $id
     *
     * @return static|null
     */
    public static function find(DbConnect $db, mixed $id): ?static
    {
        $key = static::primaryKey()->getName();
        $row = $db->fetchOne('SELECT * FROM ' . self::quote(static::table()) . ' WHERE ' . self::quote($key) . ' = ?', [$id]);

        return $row === null ? null : static::fromRow($db, $row);
    }

    /**
     * The rows whose columns equal the given values (all of them), optionally ordered by one column.
     *
     * @param DbConnect $db
     * @param array<string, mixed> $equals Column name => value; empty for every row. null matches IS NULL.
     * @param string|null $orderBy A column name.
     * @param bool $descending
     *
     * @return static[]
     */
    public static function findWhere(DbConnect $db, array $equals = [], ?string $orderBy = null, bool $descending = false): array
    {
        $conditions = [];
        $params = [];
        foreach ($equals as $name => $value) {
            static::field($name);
            if ($value === null) {
                $conditions[] = self::quote($name) . ' IS NULL';
                continue;
            }
            $conditions[] = self::quote($name) . ' = ?';
            $params[] = $value;
        }
        $sql = 'SELECT * FROM ' . self::quote(static::table());
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        if ($orderBy !== null) {
            static::field($orderBy);
            $sql .= ' ORDER BY ' . self::quote($orderBy) . ($descending ? ' DESC' : ' ASC');
        }

        return array_map(fn(array $row) => static::fromRow($db, $row), $db->fetchAll($sql, $params));
    }

    /**
     * An entity holding a row as the database returned it.
     *
     * @param DbConnect $db
     * @param array<string, mixed> $row
     *
     * @return static
     */
    protected static function fromRow(DbConnect $db, array $row): static
    {
        $entity = new static($db);
        foreach ($row as $name => $value) {
            if (isset(static::fields()[$name])) {
                $entity->values[$name] = static::fromDatabase(static::fields()[$name], $value);
            }
        }
        $entity->persistedKey = $entity->id();
        $entity->persistedValues = $entity->values;

        return $entity;
    }

    /**
     * A value as the database returned it, in PHP terms: integers as int (PDO gives text), NULL as null, everything
     * else as given (decimals stay text, so no precision is lost).
     *
     * @param Field $field
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function fromDatabase(Field $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $field->getDataType() instanceof BigIntDt && is_numeric($value) && (string)(int)$value === (string)$value
            ? (int)$value
            : $value;
    }

    /**
     * A column's value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name): mixed
    {
        static::field($name);

        return $this->values[$name];
    }

    /**
     * Set a column's value (written by the next save()).
     *
     * @param string $name
     * @param mixed $value
     *
     * @return static
     */
    public function set(string $name, mixed $value): static
    {
        static::field($name);
        $this->values[$name] = $value;

        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        return isset($this->values[$name]);
    }

    /**
     * The columns whose value differs from what the database has (every set column, for a row not saved yet).
     *
     * @return string[]
     */
    public function changedColumns(): array
    {
        $changed = [];
        foreach ($this->values as $name => $value) {
            $stored = $this->persistedValues[$name] ?? null;
            // Compared as text when both are set: PDO gives values back as text, and a value set as '1' is the 1 read
            // back. (A decimal set as 9.5 still differs from the '9.50' read back: an UPDATE that changes nothing.)
            $same = $value === null || $stored === null ? $value === $stored : (string)$value === (string)$stored;
            if (!$same || ($this->persistedKey === null && $value !== null)) {
                $changed[] = $name;
            }
        }

        return $changed;
    }

    /**
     * The primary key's value, or null for a row not saved yet.
     *
     * @return mixed
     */
    public function id(): mixed
    {
        return $this->values[static::primaryKey()->getName()] ?? null;
    }

    /**
     * Insert the row when it isn't in the database yet, otherwise update it. A row is in the database once it was
     * loaded (find, findWhere) or saved, until it is deleted; so a key the caller sets (a code, or an explicit id)
     * still inserts. After an insert an empty auto-increment key is filled in, and the row is read back. Checks the
     * values first. The write and the hooks run in one transaction (joining one already open), so what the hooks
     * write, such as a tracking row, is kept only with the change itself.
     *
     * @return static
     *
     * @throws InvalidArgumentException When a value breaks its Field's rules (all the problems, in one message).
     * @throws LogicException When updating a row that no longer exists.
     */
    public function save(): static
    {
        $this->validate();

        return $this->keepStateOnFailure(
            fn() => $this->db->transaction(
                fn() => $this->persistedKey === null ? $this->insertRow() : $this->updateRow()
            )
        );
    }

    /**
     * Run $write; when it throws (and the transaction rolled back), put this entity back as it was, so it doesn't
     * believe a write that was undone. (Inside a caller's own transaction, a later rollback of theirs isn't seen.)
     *
     * @param callable $write
     *
     * @return mixed What $write returns.
     */
    private function keepStateOnFailure(callable $write): mixed
    {
        $before = [$this->values, $this->persistedKey, $this->persistedValues];
        try {
            return $write();
        } catch (Throwable $e) {
            [$this->values, $this->persistedKey, $this->persistedValues] = $before;
            throw $e;
        }
    }

    /**
     * INSERT the row. Columns without a value are left out, so the database applies its own defaults; an
     * auto-increment key left empty is generated.
     *
     * @return static
     */
    private function insertRow(): static
    {
        $key = static::primaryKey();
        $columns = array_filter($this->values, fn(mixed $value) => $value !== null);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $this->db->insert(
            'INSERT INTO ' . self::quote(static::table()) . ' (' . implode(', ', array_map(self::quote(...), array_keys($columns)))
            . ") VALUES ($placeholders)",
            array_values($columns)
        );
        if ($this->id() === null && $key->hasAttr(Field::AUTO_INCREMENT)) {
            $this->values[$key->getName()] = static::fromDatabase($key, $this->db->lastInsertId());
        }
        $this->persistedKey = $this->id();
        // Read the row back: the database may have filled columns itself (defaults such as CURRENT_TIMESTAMP),
        // and a later update would otherwise write the stale nulls over them
        $this->refresh();
        $this->onChange(self::CREATED, array_keys($columns));
        $this->afterCreate();

        return $this;
    }

    /**
     * UPDATE the row, found by the key it was loaded or saved with (so changing the key updates the same row).
     * afterUpdate() runs only when the row changed.
     *
     * @return static
     *
     * @throws LogicException When the row no longer exists.
     */
    private function updateRow(): static
    {
        $key = static::primaryKey()->getName();
        $columns = $this->changedColumns();
        $assignments = implode(', ', array_map(fn(string $name) => self::quote($name) . ' = ?', array_keys($this->values)));
        $changed = $this->db->update(
            'UPDATE ' . self::quote(static::table()) . " SET $assignments WHERE " . self::quote($key) . ' = ?',
            [...array_values($this->values), $this->persistedKey]
        );
        if ($changed === 0) {
            // MySQL also reports 0 when nothing changed: only a missing row is an error
            if (static::find($this->db, $this->persistedKey) === null) {
                throw new LogicException(static::class . ' row ' . $this->persistedKey . ' no longer exists');
            }
            return $this;
        }
        $this->persistedKey = $this->id();
        $this->persistedValues = $this->values;
        $this->onChange(self::UPDATED, $columns);
        $this->afterUpdate();

        return $this;
    }

    /**
     * Load this row's values again from the database, by its primary key.
     *
     * @return static
     *
     * @throws LogicException When the row isn't saved yet, or no longer exists.
     */
    public function refresh(): static
    {
        if ($this->persistedKey === null) {
            throw new LogicException(static::class . ': a row not saved yet has nothing to refresh');
        }
        $fresh = static::find($this->db, $this->persistedKey)
            ?? throw new LogicException(static::class . ' row ' . $this->persistedKey . ' no longer exists');
        $this->values = $fresh->values;
        $this->persistedValues = $fresh->values;

        return $this;
    }

    /**
     * Delete the row by its primary key, in a transaction with the hooks (as save() does).
     *
     * @return bool Whether a row was deleted (false for a row never saved, or already gone).
     */
    public function delete(): bool
    {
        if ($this->persistedKey === null) {
            return false;
        }

        return $this->keepStateOnFailure(fn() => $this->db->transaction(fn() => $this->deleteRow()));
    }

    /**
     * DELETE the row, then run the hooks when there was one.
     *
     * @return bool
     */
    private function deleteRow(): bool
    {
        $deleted = $this->db->delete(
            'DELETE FROM ' . self::quote(static::table()) . ' WHERE ' . self::quote(static::primaryKey()->getName()) . ' = ?',
            [$this->persistedKey]
        ) > 0;
        // Not in the database any more either way: a later save() inserts it again
        $this->persistedKey = null;
        $this->persistedValues = [];
        if ($deleted) {
            $this->onChange(self::DELETED);
            $this->afterDelete();
        }

        return $deleted;
    }

    /**
     * Check the values against their Fields: a required NOT NULL column (a primary or unique key, other than an
     * auto-increment key) needs a value, and an unsigned one can't be negative. Other NOT NULL columns may have a
     * default the database applies (CURRENT_TIMESTAMP...), which a Field can't know, so the database checks those.
     *
     * @return void
     *
     * @throws InvalidArgumentException Listing every problem.
     */
    public function validate(): void
    {
        $problems = [];
        foreach (static::fields() as $name => $field) {
            $value = $this->values[$name];
            $generatedKey = $field->hasAttr(Field::PRIMARY_KEY) && $field->hasAttr(Field::AUTO_INCREMENT);
            if (!$generatedKey && $value === null && $field->hasAttr(Field::REQUIRED | Field::NOT_NULL)) {
                $problems[] = "$name needs a value";
            }
            if ($field->hasAttr(Field::UNSIGNED) && is_numeric($value) && $value < 0) {
                $problems[] = "$name can't be negative";
            }
        }
        if ($problems !== []) {
            throw new InvalidArgumentException(static::class . ': ' . implode('; ', $problems));
        }
    }

    /**
     * Called after every change, before the after* hook: what changed (CREATED, UPDATED, DELETED) and, for created
     * and updated, the columns written. Empty here: it is for framework behaviours such as tracking (MakeTrackable),
     * so a model's own afterCreate()/afterUpdate()/afterDelete() never replace them.
     *
     * @param string $action
     * @param string[] $columns
     *
     * @return void
     */
    protected function onChange(string $action, array $columns = []): void
    {
    }

    /**
     * Called after a new row is inserted. Empty here: for the model's own behaviour (framework ones use onChange()).
     */
    protected function afterCreate(): void
    {
    }

    /**
     * Called after a row is updated.
     */
    protected function afterUpdate(): void
    {
    }

    /**
     * Called after a row is deleted.
     */
    protected function afterDelete(): void
    {
    }

    /**
     * The values, keyed by column name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Order by primary key; 0 when it's the same row of the same kind of entity.
     *
     * @param mixed $p Another entity.
     *
     * @return int
     */
    public function compareTo($p): int
    {
        if (!is_object($p)) {
            return 1;
        }
        // The same class on both sides (not instanceof), so a->compareTo(b) and b->compareTo(a) always agree
        if ($p::class !== static::class) {
            return static::class <=> $p::class;
        }

        return $this->id() <=> $p->id();
    }

    public function __toString(): string
    {
        return static::class . ' ' . json_encode($this->values);
    }

    /**
     * The Field of a column, or a clear error for a name that isn't one.
     *
     * @param string $name
     *
     * @return Field
     *
     * @throws InvalidArgumentException
     */
    protected static function field(string $name): Field
    {
        return static::fields()[$name]
            ?? throw new InvalidArgumentException(static::class . " has no column named $name");
    }

    /**
     * A table or column name quoted for MySQL. Only names from the Fields (or the declared table) reach here.
     *
     * @param string $name
     *
     * @return string
     */
    private static function quote(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
}
