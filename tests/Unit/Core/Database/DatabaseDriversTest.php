<?php

namespace Tests\Unit\Core\Database;

use Core\Adaptors\Fakes\RecordingFake;
use Core\Adaptors\Vendor\Database\Connection;
use Core\Adaptors\Vendor\Database\QueryLog;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Database\DbConnect;
use Core\Database\PhpDbConnect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionObject;
use Tests\TestCase;

/**
 * The database behind DbConnect as an adaptor: the log driver (record and log, run nothing), testing mode using it,
 * and Connection::fake() standing in for the database. None of these need MySQL.
 *
 * @package Tests\Unit\Core\Database
 */
#[CoversClass(Connection::class)]
#[CoversClass(QueryLog::class)]
#[Group('Unit')]
#[Group('Database')]
class DatabaseDriversTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DbConnect::disconnectAll();
        Logger::fake();
    }

    public function tearDown(): void
    {
        DbConnect::disconnectAll();
        parent::tearDown();
    }

    /**
     * Given the log driver
     * When writing and reading
     * Then nothing runs: writes report 0 rows, reads come back empty, and each statement is recorded and logged.
     */
    #[Test]
    final public function logDriverRecordsAndRunsNothing(): void
    {
        $db = PhpDbConnect::instantiateDB(['connection' => 'log', 'database' => 'shop', 'username' => '']);
        $this->assertSame(0, $db->insert('INSERT INTO orders (id) VALUES (1)'));
        $this->assertFalse($db->selectAssoc('SELECT id FROM orders'));
        $log = $db->connection()->classInstance;
        $this->assertInstanceOf(QueryLog::class, $log);
        $this->assertSame(['INSERT INTO orders (id) VALUES (1)', 'SELECT id FROM orders'], $log->statements());
        Logger::assertLogged('info', 'Not run (log driver): INSERT INTO orders');
        Logger::assertLogged('info', 'Not run (log driver): SELECT id FROM orders');
    }

    /**
     * Given testing mode
     * When connecting
     * Then it uses the log driver (it replaced the old skip inside exec()).
     */
    #[Test]
    final public function testingModeUsesTheLogDriver(): void
    {
        $db = PhpDbConnect::instantiateDB(['testing' => true, 'database' => 'shop', 'username' => '']);
        $this->expectOutputRegex('/DELETE FROM orders/');
        $this->assertSame(DbConnect::LOG_DRIVER, $db->connection);
        $this->assertSame(0, $db->delete('DELETE FROM orders'));
        $this->assertSame(['DELETE FROM orders'], $db->connection()->classInstance->statements());
        unset($db);
        DbConnect::disconnectAll();
    }

    /**
     * Given a faked connection
     * When DbConnect runs statements
     * Then they reach the fake, which answers as told and can be checked (no MySQL needed).
     */
    #[Test]
    final public function theConnectionCanBeFaked(): void
    {
        $fake = Connection::fake([
            'exec' => 3,
            'query' => fn() => new RecordingFake(['fetch' => ['id' => 7]]),
        ]);
        $db = PhpDbConnect::instantiateDB(['database' => 'shop', 'username' => 'shop_user']);
        $this->assertSame(3, $db->update('UPDATE orders SET paid = 1'));
        $this->assertSame(['id' => 7], $db->selectAssoc('SELECT id FROM orders'));
        $fake->assertCalled('exec', fn(string $sql) => str_starts_with($sql, 'UPDATE orders'), 1)
            ->assertCalled('query', times: 1);
    }

    /**
     * Given a connection opened with a password
     * When looking at every property of the connection adaptor, public or not
     * Then the password is in none of them (with() keeps its arguments, so connect() clears them).
     */
    #[Test]
    final public function theConnectionDoesNotKeepThePassword(): void
    {
        Connection::fake();
        $password = 'not-kept-' . bin2hex(random_bytes(4));
        $db = PhpDbConnect::instantiateDB(['database' => 'shop', 'username' => 'shop_user', 'password' => $password]);
        $connection = $db->connection();
        $values = [];
        foreach ((new ReflectionObject($connection))->getProperties() as $property) {
            if (!$property->isStatic() && $property->isInitialized($connection)) {
                $values[$property->getName()] = $property->getValue($connection);
            }
        }
        $this->assertArrayHasKey('constructorArgs', $values);
        $this->assertStringNotContainsString($password, print_r($values, true));
    }
}
