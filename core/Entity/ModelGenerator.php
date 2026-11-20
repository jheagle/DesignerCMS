<?php

namespace Core\Entity;

use Core\Database\Schema;
use Core\Tracking\Tracking;
use ReflectionClass;
use RuntimeException;

/**
 * Writes models from the database's tables (database first), with the generation-gap pattern. For each table:
 *
 * - `<directory>/Generated/<Name>Base.php`: an abstract Entity with the table's Fields, read with Schema. It is
 *   rebuilt on every run, so it always matches the table; never edit it.
 * - `<directory>/<Name>.php`: extends the base, and is the developer's: created once, never touched again. Custom
 *   behaviour goes here (MakeTrackable, hooks, methods).
 *
 * <Name> is the table name in StudlyCase (order_items -> OrderItems). Run it with `php perform models`.
 *
 * @package Core\Entity
 */
final class ModelGenerator
{
    /**
     * Tables the framework has its own entity for.
     */
    public const FRAMEWORK_TABLES = [Tracking::TABLE];

    /**
     * Words PHP doesn't allow as a class name.
     */
    private const RESERVED = [
        'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const',
        'continue', 'declare', 'default', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
        'endforeach', 'endif', 'endswitch', 'endwhile', 'enum', 'eval', 'exit', 'extends', 'false', 'final',
        'finally', 'float', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include',
        'instanceof', 'insteadof', 'int', 'interface', 'isset', 'iterable', 'list', 'match', 'mixed', 'namespace',
        'never', 'new', 'null', 'object', 'or', 'parent', 'print', 'private', 'protected', 'public', 'readonly',
        'require', 'return', 'self', 'static', 'string', 'switch', 'throw', 'trait', 'true', 'try', 'unset', 'use',
        'var', 'void', 'while', 'xor', 'yield',
    ];

    /**
     * @param Schema $schema Reads the tables.
     * @param string $directory Where the models go (the namespace's folder).
     * @param string $namespace The models' namespace; the bases are in its Generated sub-namespace.
     * @param string[] $skip Tables never generated.
     */
    public function __construct(
        private readonly Schema $schema,
        private readonly string $directory,
        private readonly string $namespace = 'Models',
        private readonly array $skip = self::FRAMEWORK_TABLES,
    ) {
    }

    /**
     * Generate models for the given tables, or for every table in the database.
     *
     * @param string[]|null $tables
     *
     * @return GenerationReport What was written, created, left alone and skipped.
     *
     * @throws RuntimeException When a file can't be written.
     */
    public function generate(?array $tables = null): GenerationReport
    {
        $report = new GenerationReport();
        $classes = [];
        foreach ($tables ?? $this->schema->tables() as $table) {
            $class = self::className($table);
            $reason = $this->skipReason($table, $class, $classes);
            if ($reason !== null) {
                $report->skipped[$table] = $reason;
                continue;
            }
            $classes[$class] = $table;
            $fields = $this->schema->fields($table);
            $base = $this->path('Generated/' . $class . 'Base.php');
            if ($this->write($base, $this->baseSource($table, $class, $fields))) {
                $report->written[] = $base;
            } else {
                $report->unchanged[] = $base;
            }
            $model = $this->path($class . '.php');
            if (is_file($model)) {
                $report->kept[] = $model;
            } else {
                $this->write($model, $this->modelSource($table, $class));
                $report->created[] = $model;
            }
        }
        if ($tables === null) {
            $report->stale = $this->staleBases(array_keys($classes));
        }

        return $report;
    }

    /**
     * The class name for a table: StudlyCase of its words (order_items, order-items -> OrderItems).
     *
     * @param string $table
     *
     * @return string
     */
    public static function className(string $table): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', $table, -1, PREG_SPLIT_NO_EMPTY);

        return implode('', array_map(fn(string $word) => ucfirst($word), $words));
    }

    /**
     * Why a table isn't generated, or null when it is.
     *
     * @param string $table
     * @param string $class
     * @param array<string, string> $classes The classes generated so far, with their tables.
     *
     * @return string|null
     */
    private function skipReason(string $table, string $class, array $classes): ?string
    {
        if (!$this->schema->hasTable($table)) {
            return 'no such table in the database';
        }
        if (in_array($table, $this->skip, true)) {
            return 'skipped: the framework has its own entity for it';
        }
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $class) || in_array(strtolower($class), self::RESERVED, true)) {
            return "its class name ($class) isn't a valid PHP class name";
        }
        if (isset($classes[$class])) {
            return "its class name ($class) is already the model for {$classes[$class]}";
        }
        $keys = array_filter($this->schema->fields($table), fn(Field $field) => $field->hasAttr(Field::PRIMARY_KEY));

        return match (count($keys)) {
            0 => 'it has no primary key (an Entity needs one to save a row)',
            1 => null,
            default => 'its primary key has more than one column (not supported yet)',
        };
    }

    /**
     * The generated base class.
     *
     * @param string $table
     * @param string $class
     * @param Field[] $fields
     *
     * @return string
     */
    public function baseSource(string $table, string $class, array $fields): string
    {
        $imports = [Entity::class, Field::class];
        $lines = [];
        foreach ($fields as $field) {
            $dataType = get_class($field->getDataType());
            $imports[] = $dataType;
            $lines[] = $this->fieldSource($field, self::shortName($dataType)) . ',';
        }
        $imports = array_unique($imports);
        sort($imports);
        $uses = implode("\n", array_map(fn(string $import) => "use $import;", $imports));
        $tableLiteral = var_export($table, true);
        $model = $this->namespace . '\\' . $class;
        $fieldLines = implode("\n", $lines);

        return <<<PHP
            <?php

            // Generated from the `$table` table by DesignerCMS's model generator (php perform models). Don't edit it: it is
            // rebuilt from the table on every run. Your own code goes in $model.

            namespace {$this->namespace}\\Generated;

            $uses

            /**
             * The `$table` table's columns.
             */
            abstract class {$class}Base extends Entity
            {
                public const TABLE = $tableLiteral;

                protected static function fieldList(): array
                {
                    return [
            $fieldLines
                    ];
                }
            }

            PHP;
    }

    /**
     * The developer's model class, written once.
     *
     * @param string $table
     * @param string $class
     *
     * @return string
     */
    public function modelSource(string $table, string $class): string
    {
        return <<<PHP
            <?php

            namespace {$this->namespace};

            use {$this->namespace}\\Generated\\{$class}Base;

            /**
             * A row of the `$table` table. This class is yours: the model generator created it once and never changes it.
             * The columns come from {$class}Base, which is rebuilt from the table on every run.
             *
             * Add behaviour here, for example tracking every change:
             *
             *     class $class extends {$class}Base implements \\Core\\Contracts\\Trackable
             *     {
             *         use \\Core\\Traits\\MakeTrackable;
             *     }
             */
            class $class extends {$class}Base
            {
            }

            PHP;
    }

    /**
     * One Field as PHP source, indented for fieldList(): on one line, or one argument per line when that is longer
     * than 120 characters. The default is always given (Field's own default is ''), the length only when there is
     * one.
     *
     * @param Field $field
     * @param string $dataType The DataType's short (imported) name.
     *
     * @return string
     */
    private function fieldSource(Field $field, string $dataType): string
    {
        $arguments = [
            var_export($field->getName(), true),
            "$dataType::class",
            'default: ' . self::literal($field->getDefault()),
        ];
        if ($field->getLength() !== null) {
            $arguments[] = 'length: ' . $field->getLength();
        }
        $arguments[] = 'attributes: ' . self::flags($field);
        $arguments[] = 'sqlType: ' . self::literal($field->getSqlType());
        if (!$field->isTypeSupported()) {
            $arguments[] = 'typeSupported: false';
        }
        $indent = str_repeat(' ', 12);
        $line = $indent . 'new Field(' . implode(', ', $arguments) . ')';
        if (strlen($line) + 1 <= 120) {
            return $line;
        }
        $inner = str_repeat(' ', 16);

        return $indent . "new Field(\n$inner" . implode(",\n$inner", $arguments) . ",\n$indent)";
    }

    /**
     * A Field's attributes as Field constants (Field::PRIMARY_KEY | Field::NOT_NULL), leaving out REQUIRED where
     * Field adds it itself (primary and unique keys).
     *
     * @param Field $field
     *
     * @return string
     */
    private static function flags(Field $field): string
    {
        $implied = $field->hasAttr(Field::PRIMARY_KEY) || $field->hasAttr(Field::UNIQUE) ? Field::REQUIRED : 0;
        $names = [];
        foreach ((new ReflectionClass(Field::class))->getConstants() as $name => $value) {
            if (is_int($value) && $value !== $implied && $field->hasAttr($value)) {
                $names[] = "Field::$name";
            }
        }

        return $names === [] ? '0' : implode(' | ', $names);
    }

    /**
     * A value as PHP source.
     *
     * @param mixed $value
     *
     * @return string
     */
    private static function literal(mixed $value): string
    {
        return $value === null ? 'null' : var_export($value, true);
    }

    /**
     * A class's name without its namespace.
     *
     * @param string $class
     *
     * @return string
     */
    private static function shortName(string $class): string
    {
        return substr(strrchr('\\' . $class, '\\'), 1);
    }

    /**
     * Base classes in Generated/ whose table wasn't generated this run (the table was dropped or renamed). Reported,
     * never deleted: removing them is the developer's call.
     *
     * @param string[] $classes The classes generated this run.
     *
     * @return string[]
     */
    private function staleBases(array $classes): array
    {
        $stale = [];
        foreach (glob($this->path('Generated/*Base.php')) ?: [] as $file) {
            if (!in_array(basename($file, 'Base.php'), $classes, true)) {
                $stale[] = $file;
            }
        }

        return $stale;
    }

    /**
     * A path in the models directory.
     *
     * @param string $relative
     *
     * @return string
     */
    private function path(string $relative): string
    {
        return rtrim($this->directory, '/') . '/' . $relative;
    }

    /**
     * Write $source to $file, creating folders as needed. Nothing is written when the file already holds it.
     *
     * @param string $file
     * @param string $source
     *
     * @return bool Whether the file was written.
     *
     * @throws RuntimeException When it can't be written.
     */
    private function write(string $file, string $source): bool
    {
        if (is_file($file) && file_get_contents($file) === $source) {
            return false;
        }
        $folder = dirname($file);
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
            throw new RuntimeException("Can't create the folder $folder");
        }
        if (file_put_contents($file, $source) === false) {
            throw new RuntimeException("Can't write $file");
        }

        return true;
    }
}
