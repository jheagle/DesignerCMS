<?php

namespace Tests\Unit\Core\Entity;

use Core\Database\PhpDbConnect;
use Core\Database\Schema;
use Core\Entity\Entity;
use Core\Entity\Field;
use Core\Entity\GenerationReport;
use Core\Entity\ModelGenerator;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;
use Tests\Traits\UsesTestDatabase;

/**
 * The model generator against real tables in the MySQL test database (Schema reads INFORMATION_SCHEMA, which has no
 * temporary tables), writing into a temporary folder.
 *
 * @package Tests\Unit\Core\Entity
 */
#[CoversClass(ModelGenerator::class)]
#[CoversClass(GenerationReport::class)]
#[Group('Unit')]
#[Group('Database')]
class ModelGeneratorTest extends TestCase
{
    use UsesTestDatabase;

    private PhpDbConnect $db;

    private Schema $schema;

    /** A suffix making this test's tables, folder and namespace its own. */
    private string $suffix;

    private string $directory;

    private string $namespace;

    /** @var string[] Tables to drop after the test. */
    private array $tables = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->connectTestDatabase();
        $this->schema = new Schema($this->db);
        $this->suffix = bin2hex(random_bytes(3));
        $this->directory = sys_get_temp_dir() . "/designercms-models-$this->suffix";
        $this->namespace = "GeneratorTest$this->suffix\\Models";
    }

    public function tearDown(): void
    {
        if (isset($this->db) && $this->tables !== []) {
            $this->db->alter('DROP TABLE IF EXISTS ' . implode(', ', array_map(fn($t) => "`$t`", $this->tables)));
        }
        if (is_dir($this->directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->directory);
        }
        $this->disconnectTestDatabase();
        parent::tearDown();
    }

    /**
     * Create a real table for this test; its name gets the test's suffix.
     *
     * @param string $name
     * @param string $columns
     *
     * @return string The table's name.
     */
    private function table(string $name, string $columns): string
    {
        $table = "{$name}_$this->suffix";
        $this->tables[] = $table;
        $this->db->alter("CREATE TABLE `$table` ($columns) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        return $table;
    }

    private function generator(): ModelGenerator
    {
        return new ModelGenerator($this->schema, $this->directory, $this->namespace);
    }

    /**
     * Load a generated model (base first), and give its class name.
     *
     * @param string $table
     *
     * @return class-string<Entity>
     */
    private function load(string $table): string
    {
        $class = ModelGenerator::className($table);
        require_once "$this->directory/Generated/{$class}Base.php";
        require_once "$this->directory/$class.php";

        return "$this->namespace\\$class";
    }

    /**
     * Everything a Field says about its column.
     *
     * @param Field[] $fields
     *
     * @return array
     */
    private static function describe(array $fields): array
    {
        return array_map(fn(Field $field) => [
            $field->getName(),
            get_class($field->getDataType()),
            $field->getDefault(),
            $field->getLength(),
            $field->getAttributes(),
            $field->getSqlType(),
            $field->isTypeSupported(),
        ], $fields);
    }

    /**
     * Given table names
     * When they become class names
     * Then each word is capitalised and the separators go.
     */
    #[Test]
    final public function classNamesAreStudlyCase(): void
    {
        $this->assertSame('OrderItems', ModelGenerator::className('order_items'));
        $this->assertSame('OrderItems', ModelGenerator::className('order-items'));
        $this->assertSame('Tracking', ModelGenerator::className('tracking'));
        $this->assertSame('ApiV2Keys', ModelGenerator::className('api_v2_keys'));
    }

    /**
     * Given a table with every kind of column (supported types, unsupported ones, keys, defaults, a negative default)
     * When its model is generated and loaded
     * Then the generated base's Fields say exactly what Schema reads from the table, the developer's class extends
     * it, and the report lists both files as new.
     */
    #[Test]
    final public function theGeneratedBaseMatchesTheTable(): void
    {
        $table = $this->table('gen_orders', "
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            code CHAR(2) NOT NULL DEFAULT 'CA',
            email VARCHAR(80) NOT NULL,
            nickname VARCHAR(30) NULL,
            qty SMALLINT UNSIGNED ZEROFILL NOT NULL DEFAULT 7,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            big BIGINT NOT NULL DEFAULT -5,
            body TEXT,
            status ENUM('new','paid') NOT NULL DEFAULT 'new',
            note VARCHAR(20) NOT NULL DEFAULT 'it''s \\\\ fine',
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_email (email),
            KEY idx_code_status (code, status),
            FULLTEXT KEY ft_body (body)
        ");
        $report = $this->generator()->generate([$table]);

        $class = $this->load($table);
        $this->assertSame(self::describe($this->schema->fields($table)), self::describe($class::fields()));
        $this->assertSame($table, $class::table());
        $this->assertTrue(is_subclass_of($class, "$this->namespace\\Generated\\" . ModelGenerator::className($table) . 'Base'));
        $name = ModelGenerator::className($table);
        $this->assertSame("GenOrders" . ucfirst($this->suffix), $name);
        $this->assertSame(["$this->directory/Generated/{$name}Base.php"], $report->written);
        $this->assertSame(["$this->directory/$name.php"], $report->created);
        $this->assertSame([], $report->skipped);
    }

    /**
     * Given a generated model
     * When it saves and finds rows
     * Then it works as any Entity: the generated class is a real model.
     */
    #[Test]
    final public function theGeneratedModelReadsAndWrites(): void
    {
        $table = $this->table('gen_people', '
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            age TINYINT UNSIGNED NULL
        ');
        $this->generator()->generate([$table]);
        $class = $this->load($table);

        $person = (new $class($this->db, ['name' => 'Ada', 'age' => 36]))->save();
        $found = $class::find($this->db, $person->id());
        $this->assertSame('Ada', $found->name);
        $this->assertSame(36, $found->age);
    }

    /**
     * Given generated models, and a developer's change to their class
     * When the generator runs again, then again after the table gains a column
     * Then an unchanged base isn't rewritten, a changed table rewrites its base, and the developer's class is never
     * touched.
     */
    #[Test]
    final public function rerunsRebuildTheBaseAndKeepTheDevelopersClass(): void
    {
        $table = $this->table('gen_tags', 'id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, label VARCHAR(20) NOT NULL');
        $this->generator()->generate([$table]);
        $class = ModelGenerator::className($table);
        $model = "$this->directory/$class.php";
        $base = "$this->directory/Generated/{$class}Base.php";
        file_put_contents($model, str_replace("{\n}", "{\n    // mine\n}", file_get_contents($model)));
        $mine = file_get_contents($model);

        $again = $this->generator()->generate([$table]);
        $this->assertSame([$base], $again->unchanged);
        $this->assertSame([$model], $again->kept);
        $this->assertSame([], $again->written);
        $this->assertSame([], $again->created);

        $this->db->alter("ALTER TABLE `$table` ADD COLUMN colour VARCHAR(10) NULL");
        $changed = $this->generator()->generate([$table]);
        $this->assertSame([$base], $changed->written);
        $this->assertStringContainsString("'colour'", file_get_contents($base));
        $this->assertSame($mine, file_get_contents($model), "the developer's class is never rewritten");
    }

    /**
     * Given tables a model can't be made for
     * When they are asked for
     * Then each is skipped with its reason, and nothing is written for them.
     */
    #[Test]
    final public function tablesThatCantBeModelsAreSkippedWithAReason(): void
    {
        $noKey = $this->table('gen_log', 'line VARCHAR(50) NOT NULL');
        $composite = $this->table('gen_pairs', 'a INT NOT NULL, b INT NOT NULL, PRIMARY KEY (a, b)');
        $framework = $this->table('gen_framework', 'id INT NOT NULL PRIMARY KEY');
        $this->tables[] = 'list';
        $this->db->alter('DROP TABLE IF EXISTS `list`');
        $this->db->alter('CREATE TABLE `list` (id INT NOT NULL PRIMARY KEY)');
        $first = $this->table('gen_a_b', 'id INT NOT NULL PRIMARY KEY');
        $clash = $this->table('gen-a-b', 'id INT NOT NULL PRIMARY KEY');

        $report = (new ModelGenerator($this->schema, $this->directory, $this->namespace, [$framework]))
            ->generate([$noKey, $composite, $framework, 'list', 'no_such_table', $first, $clash]);

        $this->assertStringContainsString('no primary key', $report->skipped[$noKey]);
        $this->assertStringContainsString('more than one column', $report->skipped[$composite]);
        $this->assertStringContainsString('framework', $report->skipped[$framework]);
        $this->assertStringContainsString("isn't a valid PHP class name", $report->skipped['list']);
        $this->assertStringContainsString('no such table', $report->skipped['no_such_table']);
        $this->assertStringContainsString("already the model for $first", $report->skipped[$clash]);
        $this->assertCount(1, $report->written, 'only the first of the clashing pair');
        $this->assertContains('tracking', ModelGenerator::FRAMEWORK_TABLES);
    }

    /**
     * Given a base class left from a table that no longer exists
     * When every table is generated
     * Then that base is reported as stale, and left in place.
     */
    #[Test]
    final public function leftoverBasesAreReportedNotDeleted(): void
    {
        $table = $this->table('gen_items', 'id INT NOT NULL PRIMARY KEY');
        mkdir("$this->directory/Generated", 0775, true);
        $old = "$this->directory/Generated/DroppedTableBase.php";
        file_put_contents($old, '<?php');

        $report = $this->generator()->generate();

        $this->assertSame([$old], $report->stale);
        $this->assertFileExists($old);
        $this->assertContains("$this->directory/Generated/" . ModelGenerator::className($table) . 'Base.php', $report->written);
    }

    /**
     * Given the test database and a models folder in the environment
     * When php perform models runs for one table
     * Then it writes that table's model and lists what it did.
     */
    #[Test]
    final public function performModelsRunsTheGenerator(): void
    {
        $table = $this->table('gen_cli', 'id INT NOT NULL PRIMARY KEY');
        $root = dirname(__DIR__, 4);
        $env = array_merge(getenv(), [
            'DB_DATABASE' => $_ENV['DB_DATABASE'],
            // Real queries (not the log driver), and no debug output
            'TESTING' => 'false',
            'PRODUCTION' => 'true',
            'MODELS_PATH' => $this->directory,
            'MODELS_NAMESPACE' => $this->namespace,
        ]);
        $process = proc_open(
            // EGPCS: environment variables reach $_ENV (envGet()), and .env doesn't replace them
            [PHP_BINARY, '-d', 'variables_order=EGPCS', "$root/perform", 'models', $table],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
            $env
        );
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        $status = proc_close($process);

        $this->assertSame(0, $status, $output);
        $this->assertStringContainsString('Created (yours to edit)', $output);
        $this->assertFileExists("$this->directory/Generated/" . ModelGenerator::className($table) . 'Base.php');
    }
}
