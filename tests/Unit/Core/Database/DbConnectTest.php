<?php

namespace Tests\Unit\Core\Database;

use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Database\DbConnect;
use Core\Database\PhpDbConnect;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionObject;
use Tests\Mocks\GenericClass;
use Tests\TestCase;
use Tests\Traits\UsesTestDatabase;

/**
 * Connecting: keyed settings, settings from config, one shared connection per class and database, and failures that
 * throw instead of leaving a half-made object.
 *
 * @package Tests\Unit\Core\Database
 */
#[Group('Unit')]
#[Group('Database')]
class DbConnectTest extends TestCase
{
    use UsesTestDatabase;

    private PhpDbConnect $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->connectTestDatabase();
    }

    public function tearDown(): void
    {
        $this->disconnectTestDatabase();
        parent::tearDown();
    }

    /**
     * Given the test settings in phpunit.xml and .env
     * When connecting with no arguments
     * Then the connection is to the test database, and queries really run.
     */
    #[Test]
    final public function connectsToTheConfiguredDatabase(): void
    {
        $this->assertSame('designercms_test', $this->db->database);
        $this->assertFalse($this->db->testing);
        $this->assertSame(['name' => 'designercms_test'], $this->db->selectAssoc('SELECT DATABASE() AS name'));
    }

    /**
     * Given a connection already made
     * When asking for the same database again
     * Then the same object comes back.
     */
    #[Test]
    final public function reusesTheConnection(): void
    {
        $this->assertSame($this->db, PhpDbConnect::instantiateDB());
        $this->assertSame($this->db, PhpDbConnect::instantiateDB(['database' => 'designercms_test']));
    }

    /**
     * Given keyed settings
     * When they name a setting
     * Then it replaces config's value, and the rest still come from config.
     */
    #[Test]
    final public function keyedSettingsReplaceConfig(): void
    {
        DbConnect::disconnectAll();
        $db = PhpDbConnect::instantiateDB(['production' => false, 'hostname' => '127.0.0.1']);
        $this->expectOutputRegex('/Connected to database \(designercms_test\)/');
        $this->assertFalse($db->production);
        $this->assertSame('127.0.0.1', $db->hostname);
        $this->assertSame('designercms_test', $db->database);
        // Its "Completed 0 Queries" goodbye is printed now, inside this test, not after it
        unset($db);
        DbConnect::disconnectAll();
    }

    /**
     * Given no database name
     * When connecting
     * Then it throws, and nothing is kept.
     */
    #[Test]
    final public function noDatabaseNameThrows(): void
    {
        DbConnect::disconnectAll();
        $this->expectException(InvalidArgumentException::class);
        try {
            PhpDbConnect::instantiateDB(['database' => '']);
        } finally {
            $this->assertSame([], DbConnect::$instance);
        }
    }

    /**
     * Given a wrong password
     * When connecting
     * Then the PDO error is thrown, and nothing is kept.
     */
    #[Test]
    final public function failedConnectionThrows(): void
    {
        DbConnect::disconnectAll();
        $this->expectException(PDOException::class);
        try {
            PhpDbConnect::instantiateDB(['password' => 'not-the-password']);
        } finally {
            $this->assertSame([], DbConnect::$instance);
            $this->assertSame([], DbConnect::$connections);
        }
    }

    /**
     * Given the .env strings 'false' and 'true'
     * When config reads TESTING and PRODUCTION
     * Then they are the booleans they say ((bool) 'false' would be true).
     */
    #[Test]
    final public function systemConfigReadsBooleanStrings(): void
    {
        $saved = [$_ENV['TESTING'] ?? null, $_ENV['PRODUCTION'] ?? null];
        try {
            $_ENV['TESTING'] = 'false';
            $_ENV['PRODUCTION'] = 'true';
            Config::reset();
            $this->assertFalse(PhpDbConnect::configuredSettings()['testing']);
            $this->assertTrue(PhpDbConnect::configuredSettings()['production']);
            $_ENV['TESTING'] = '1';
            $_ENV['PRODUCTION'] = 'off';
            Config::reset();
            $this->assertTrue(PhpDbConnect::configuredSettings()['testing']);
            $this->assertFalse(PhpDbConnect::configuredSettings()['production']);
        } finally {
            [$_ENV['TESTING'], $_ENV['PRODUCTION']] = $saved;
            Config::reset();
        }
    }

    /**
     * Given a connection
     * When looking at every property, public or not
     * Then the password is in none of them.
     */
    #[Test]
    final public function passwordIsNotKept(): void
    {
        $password = PhpDbConnect::configuredSettings()['password'];
        $this->assertNotSame('', $password, 'The test database needs a password for this check to mean anything');
        $values = [];
        foreach ((new ReflectionObject($this->db))->getProperties() as $property) {
            if (!$property->isStatic() && $property->isInitialized($this->db)) {
                $values[$property->getName()] = $property->getValue($this->db);
            }
        }
        $this->assertStringNotContainsString($password, print_r($values, true));
    }

    /**
     * Given no database user
     * When connecting
     * Then it throws a clear error instead of trying an anonymous login.
     */
    #[Test]
    final public function noUserThrows(): void
    {
        DbConnect::disconnectAll();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DB_USERNAME');
        PhpDbConnect::instantiateDB(['username' => '']);
    }

    /**
     * Given a failed connection, with exception arguments switched on (as in many dev setups)
     * When it is logged and thrown
     * Then the password is in neither the log nor the exception's trace.
     */
    #[Test]
    final public function failedConnectionDoesNotLeakThePassword(): void
    {
        $password = 'not-the-password-' . bin2hex(random_bytes(4));
        $logged = [];
        Logger::setResource(new GenericClass([
            'error' => function ($message, $context = []) use (&$logged) {
                $logged[] = [$message, $context];
            },
            'info' => fn() => null,
        ]));
        $ignoreArgs = ini_get('zend.exception_ignore_args');
        ini_set('zend.exception_ignore_args', '0');
        DbConnect::disconnectAll();
        try {
            PhpDbConnect::instantiateDB(['password' => $password]);
            $this->fail('The connection should have failed');
        } catch (PDOException $e) {
            $this->assertStringNotContainsString($password, print_r($e->getTrace(), true));
            $this->assertStringNotContainsString($password, (string)$e);
        } finally {
            ini_set('zend.exception_ignore_args', (string)$ignoreArgs);
        }
        $this->assertNotEmpty($logged);
        $this->assertStringNotContainsString($password, print_r($logged, true));
    }

    /**
     * Given two connections with the same database name on different hosts
     * When the second is made
     * Then it does not borrow the first one's PDO.
     */
    #[Test]
    final public function sameDatabaseNameOnAnotherHostIsAnotherConnection(): void
    {
        $this->expectException(PDOException::class);
        // Same database name, another host (nothing listens on this port): must try to connect, not reuse
        PhpDbConnect::instantiateDB(['hostname' => '127.0.0.1;port=1']);
    }
}
