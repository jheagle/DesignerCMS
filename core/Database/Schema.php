<?php

namespace Core\Database;

use Core\DataTypes\Numbers\Doubles\DecimalDt;
use Core\DataTypes\Numbers\Doubles\DoubleDt;
use Core\DataTypes\Numbers\Doubles\FloatDt;
use Core\DataTypes\Numbers\Integers\BigIntDt;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Numbers\Integers\MediumIntDt;
use Core\DataTypes\Numbers\Integers\SmallIntDt;
use Core\DataTypes\Numbers\Integers\TinyIntDt;
use Core\DataTypes\Strings\CharDt;
use Core\DataTypes\Strings\StringDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Field;
use InvalidArgumentException;

/**
 * Read a table's structure from the database into Fields (database first: tables are designed in MySQL, and models
 * are generated from what this reads). It asks INFORMATION_SCHEMA about the current database, through prepared
 * statements.
 *
 * Types without a DataType class yet (dates, TEXT/BLOB, ENUM/SET, JSON, BIT, BINARY: DESI-4) are still read: the
 * Field holds them as a StringDt, marks them isTypeSupported() false, and keeps the exact type in getSqlType().
 *
 * @package Core\Database
 */
class Schema
{
    /**
     * The DataType for each MySQL type that has one.
     */
    public const TYPES = [
        'tinyint' => TinyIntDt::class,
        'smallint' => SmallIntDt::class,
        'mediumint' => MediumIntDt::class,
        'int' => IntDt::class,
        'integer' => IntDt::class,
        'bigint' => BigIntDt::class,
        'decimal' => DecimalDt::class,
        'numeric' => DecimalDt::class,
        'double' => DoubleDt::class,
        'real' => DoubleDt::class,
        'float' => FloatDt::class,
        'char' => CharDt::class,
        'varchar' => VarCharDt::class,
    ];

    public function __construct(private readonly DbConnect $db)
    {
    }

    /**
     * Whether the table exists in the current database.
     *
     * @param string $table
     *
     * @return bool
     */
    public function hasTable(string $table): bool
    {
        return (int)$this->db->fetchValue(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$table]
        ) > 0;
    }

    /**
     * The table's columns as Fields, in column order, keyed by column name.
     *
     * @param string $table
     *
     * @return array<string, Field>
     *
     * @throws InvalidArgumentException When the table doesn't exist.
     */
    public function fields(string $table): array
    {
        $columns = $this->db->fetchAll(
            'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH, EXTRA,'
            . ' COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$table]
        );
        if ($columns === []) {
            throw new InvalidArgumentException("No table named $table in the current database");
        }
        $indexed = $this->indexFlags($table);
        $fields = [];
        foreach ($columns as $column) {
            $field = $this->field($column, $indexed[$column['COLUMN_NAME']] ?? 0);
            $fields[$field->getName()] = $field;
        }

        return $fields;
    }

    /**
     * The table's indexes, by name, with their columns in order: what per-column Field flags can't say on their own
     * (which columns form one composite index, and its name).
     *
     * @param string $table
     *
     * @return array<string, array{columns: string[], unique: bool, type: string}> type: BTREE, FULLTEXT, HASH, SPATIAL
     */
    public function indexes(string $table): array
    {
        $rows = $this->db->fetchAll(
            'SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE, INDEX_TYPE FROM INFORMATION_SCHEMA.STATISTICS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            [$table]
        );
        $indexes = [];
        foreach ($rows as $row) {
            $indexes[$row['INDEX_NAME']] ??= [
                'columns' => [],
                'unique' => (int)$row['NON_UNIQUE'] === 0,
                'type' => $row['INDEX_TYPE'],
            ];
            $indexes[$row['INDEX_NAME']]['columns'][] = $row['COLUMN_NAME'];
        }

        return $indexes;
    }

    /**
     * One column as a Field.
     *
     * @param array $column A row of INFORMATION_SCHEMA.COLUMNS.
     * @param int $indexFlags Field flags from the indexes the column is in.
     *
     * @return Field
     */
    private function field(array $column, int $indexFlags): Field
    {
        $dataType = strtolower($column['DATA_TYPE']);
        $sqlType = strtolower($column['COLUMN_TYPE']);
        $class = self::TYPES[$dataType] ?? null;
        $attributes = $indexFlags;
        if ($column['IS_NULLABLE'] === 'NO') {
            $attributes |= Field::NOT_NULL;
        }
        if (str_contains($sqlType, 'unsigned')) {
            $attributes |= Field::UNSIGNED;
        }
        if (str_contains($sqlType, 'zerofill')) {
            $attributes |= Field::ZERO_FILL;
        }
        if (str_contains(strtolower($column['EXTRA']), 'auto_increment')) {
            $attributes |= Field::AUTO_INCREMENT;
        }
        if ($column['COLUMN_KEY'] === 'PRI') {
            $attributes |= Field::PRIMARY_KEY;
        }
        // A computed default (CURRENT_TIMESTAMP and the like) is the database's to apply, not a value to hold
        $generated = str_contains(strtoupper($column['EXTRA']), 'DEFAULT_GENERATED');
        $default = $generated ? null : $column['COLUMN_DEFAULT'];
        $length = $column['CHARACTER_MAXIMUM_LENGTH'] !== null && $class !== null
            ? (int)$column['CHARACTER_MAXIMUM_LENGTH']
            : null;

        return new Field(
            $column['COLUMN_NAME'],
            $class ?? StringDt::class,
            // No default (or NULL): an empty value of the column's kind. Whether NULL is allowed is the NOT_NULL flag
            $default ?? (is_subclass_of($class ?? StringDt::class, StringDt::class) || $class === null ? '' : 0),
            $length,
            $attributes,
            $sqlType,
            $class !== null
        );
    }

    /**
     * Field flags for each column from the table's indexes: UNIQUE for a single-column unique index (a composite one
     * is unique only as a whole), INDEX for any other index, FULLTEXT for a full-text one. The primary key comes from
     * the column itself.
     *
     * @param string $table
     *
     * @return array<string, int>
     */
    private function indexFlags(string $table): array
    {
        $flags = [];
        foreach ($this->indexes($table) as $name => $index) {
            if ($name === 'PRIMARY') {
                continue;
            }
            $flag = match (true) {
                $index['type'] === 'FULLTEXT' => Field::FULLTEXT,
                $index['unique'] && count($index['columns']) === 1 => Field::UNIQUE,
                default => Field::INDEX,
            };
            foreach ($index['columns'] as $column) {
                $flags[$column] = ($flags[$column] ?? 0) | $flag;
            }
        }

        return $flags;
    }
}
