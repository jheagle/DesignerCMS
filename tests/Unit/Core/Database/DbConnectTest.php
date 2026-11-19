<?php

namespace Tests\Unit\Core\Database;

use Core\Adaptors\Config;
use Core\Database\DbConnect;
use Core\Database\PhpDbConnect;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
        $db = PhpDbConnect::instantiateDB(['production' => false, 'testing' => true]);
        $this->expectOutputRegex('/Connected to database \(designercms_test\)/');
        $this->assertTrue($db->testing);
        $this->assertFalse($db->production);
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
            $this->assertSame([], DbConnect::$pdoInstance);
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
     * When it is turned into a string or settings are applied
     * Then the password is nowhere on the object.
     */
    #[Test]
    final public function passwordIsNotKept(): void
    {
        $this->assertArrayNotHasKey('password', get_object_vars($this->db));
        $this->assertArrayNotHasKey('username', get_object_vars($this->db));
    }
}
