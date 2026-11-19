<?php

namespace Tests\Unit\Core\Database;

use Core\Adaptors\Vendor\Logger\Logger;
use Core\Database\PhpDbConnect;
use PDOException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use Tests\Traits\UsesTestDatabase;

/**
 * Prepared statements and transactions against the MySQL test database: values are bound, never pasted into SQL.
 *
 * @package Tests\Unit\Core\Database
 */
#[Group('Unit')]
#[Group('Database')]
class PreparedStatementsTest extends TestCase
{
    use UsesTestDatabase;

    private PhpDbConnect $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->connectTestDatabase();
        $this->db->alter('CREATE TEMPORARY TABLE people (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100))');
    }

    public function tearDown(): void
    {
        $this->disconnectTestDatabase();
        parent::tearDown();
    }

    /**
     * Given a value that would break SQL pasted into it
     * When it is inserted with a placeholder
     * Then it is stored exactly as given, and the statement it imitates never runs.
     */
    #[Test]
    final public function valuesAreBoundNotPasted(): void
    {
        $hostile = "Robert'); DROP TABLE people; --";
        $this->assertSame(1, $this->db->insert('INSERT INTO people (name) VALUES (?)', [$hostile]));
        $this->assertSame($hostile, $this->db->fetchValue('SELECT name FROM people WHERE id = ?', [1]));
        $this->assertSame(1, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given positional and named placeholders
     * When rows are written and read
     * Then both bind, and the fetch helpers return all rows, one row, or one value (null when there is nothing).
     */
    #[Test]
    final public function placeholdersAndFetchHelpers(): void
    {
        $this->db->insert('INSERT INTO people (name) VALUES (?), (?)', ['Ada', 'Grace']);
        $this->db->insert('INSERT INTO people (name) VALUES (:name)', ['name' => 'Linus']);
        $this->assertSame(
            [['id' => 1, 'name' => 'Ada'], ['id' => 2, 'name' => 'Grace'], ['id' => 3, 'name' => 'Linus']],
            array_map(fn(array $row) => ['id' => (int)$row['id'], 'name' => $row['name']], $this->db->fetchAll('SELECT id, name FROM people ORDER BY id'))
        );
        $this->assertSame('Grace', $this->db->fetchOne('SELECT name FROM people WHERE id = :id', ['id' => 2])['name']);
        $this->assertNull($this->db->fetchOne('SELECT name FROM people WHERE id = ?', [99]));
        $this->assertNull($this->db->fetchValue('SELECT name FROM people WHERE id = ?', [99]));
        $this->assertSame(1, $this->db->update('UPDATE people SET name = ? WHERE id = ?', ['Ada L.', 1]));
        $this->assertSame(1, $this->db->delete('DELETE FROM people WHERE name = ?', ['Linus']));
        $this->assertSame(2, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given rows
     * When reading them one call at a time with selectAssoc
     * Then each call gives the next row until false, and different values start the query again.
     */
    #[Test]
    final public function selectReadsRowByRow(): void
    {
        $this->db->insert('INSERT INTO people (name) VALUES (?), (?), (?)', ['a', 'b', 'c']);
        $names = [];
        while ($row = $this->db->selectAssoc('SELECT name FROM people WHERE id >= ? ORDER BY id', [2])) {
            $names[] = $row['name'];
        }
        $this->assertSame(['b', 'c'], $names);
        $this->assertSame('a', $this->db->selectAssoc('SELECT name FROM people WHERE id >= ? ORDER BY id', [1])['name']);
    }

    /**
     * Given a statement MySQL rejects
     * When it runs
     * Then it throws, and the log has the SQL but not the values.
     */
    #[Test]
    final public function failuresThrowAndLogTheSqlWithoutTheValues(): void
    {
        try {
            $this->db->insert('INSERT INTO no_such_table (name) VALUES (?)', ['secret-value']);
            $this->fail('The statement should have failed');
        } catch (PDOException) {
        }
        Logger::assertLogged('error', 'Statement failed');
        $this->assertStringContainsString(
            'no_such_table',
            print_r(Logger::assertLogged('error')->calls('error'), true)
        );
        $this->assertStringNotContainsString('secret-value', print_r(Logger::assertLogged('error')->calls(), true));
    }

    /**
     * Given work inside transaction()
     * When it returns
     * Then everything is kept and its return value comes back.
     */
    #[Test]
    final public function transactionCommits(): void
    {
        $result = $this->db->transaction(function (PhpDbConnect $db) {
            $db->insert('INSERT INTO people (name) VALUES (?)', ['kept']);
            return 'done';
        });
        $this->assertSame('done', $result);
        $this->assertFalse($this->db->inTransaction());
        $this->assertSame('kept', $this->db->fetchValue('SELECT name FROM people'));
    }

    /**
     * Given work inside transaction() that throws part way
     * When it fails
     * Then nothing it did is kept, and the error comes back out.
     */
    #[Test]
    final public function transactionRollsBackAndRethrows(): void
    {
        try {
            $this->db->transaction(function (PhpDbConnect $db) {
                $db->insert('INSERT INTO people (name) VALUES (?)', ['undone']);
                throw new RuntimeException('part way');
            });
            $this->fail('The error should come back out');
        } catch (RuntimeException $e) {
            $this->assertSame('part way', $e->getMessage());
        }
        $this->assertFalse($this->db->inTransaction());
        $this->assertSame(0, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given begin, writes and rollBack by hand
     * When rolled back
     * Then the writes are gone.
     */
    #[Test]
    final public function beginAndRollBackByHand(): void
    {
        $this->db->begin();
        $this->assertTrue($this->db->inTransaction());
        $this->db->insert('INSERT INTO people (name) VALUES (?)', ['temporary']);
        $this->db->rollBack();
        $this->assertSame(0, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given a loop that read every row
     * When the same query is read again (after another row was added)
     * Then it runs again and sees the rows as they are now (it used to return false forever).
     */
    #[Test]
    final public function theSameQueryCanBeReadAgain(): void
    {
        $this->db->insert('INSERT INTO people (name) VALUES (?), (?)', ['a', 'b']);
        $read = function (): array {
            $names = [];
            while ($row = $this->db->selectAssoc('SELECT name FROM people ORDER BY id')) {
                $names[] = $row['name'];
            }
            return $names;
        };
        $this->assertSame(['a', 'b'], $read());
        $this->db->insert('INSERT INTO people (name) VALUES (?)', ['c']);
        $this->assertSame(['a', 'b', 'c'], $read());
    }

    /**
     * Given a transaction inside another
     * When both finish
     * Then the inner one was part of the outer, and everything is kept.
     */
    #[Test]
    final public function nestedTransactionsArePartOfTheOuterOne(): void
    {
        $this->db->transaction(function (PhpDbConnect $db) {
            $db->insert('INSERT INTO people (name) VALUES (?)', ['outer']);
            $db->transaction(fn(PhpDbConnect $inner) => $inner->insert('INSERT INTO people (name) VALUES (?)', ['inner']));
            $this->assertTrue($db->inTransaction(), 'still in the outer transaction after the inner one');
        });
        $this->assertSame(2, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given a failure in an inner transaction
     * When it reaches the outer one
     * Then nothing from either is kept.
     */
    #[Test]
    final public function aNestedFailureUndoesEverything(): void
    {
        try {
            $this->db->transaction(function (PhpDbConnect $db) {
                $db->insert('INSERT INTO people (name) VALUES (?)', ['outer']);
                $db->transaction(function (PhpDbConnect $inner) {
                    $inner->insert('INSERT INTO people (name) VALUES (?)', ['inner']);
                    throw new RuntimeException('inner failed');
                });
            });
            $this->fail('The error should come back out');
        } catch (RuntimeException $e) {
            $this->assertSame('inner failed', $e->getMessage());
        }
        $this->assertSame(0, (int)$this->db->fetchValue('SELECT COUNT(*) FROM people'));
    }

    /**
     * Given an inner transaction that fails, and outer work that catches the error and carries on
     * When the outer one commits
     * Then only the inner work is undone (a savepoint): the outer work, before and after it, is kept.
     */
    #[Test]
    final public function aCaughtNestedFailureUndoesOnlyItsOwnPart(): void
    {
        $this->db->transaction(function (PhpDbConnect $db) {
            $db->insert('INSERT INTO people (name) VALUES (?)', ['before']);
            try {
                $db->transaction(function (PhpDbConnect $inner) {
                    $inner->insert('INSERT INTO people (name) VALUES (?)', ['inner']);
                    throw new RuntimeException('inner failed');
                });
            } catch (RuntimeException) {
            }
            $db->insert('INSERT INTO people (name) VALUES (?)', ['after']);
        });
        $this->assertSame(
            ['before', 'after'],
            array_column($this->db->fetchAll('SELECT name FROM people ORDER BY id'), 'name')
        );
    }
}
