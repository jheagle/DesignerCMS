<?php

namespace Tests\Traits;

use Core\Adaptors\Config;
use Core\Database\DbConnect;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Database\PhpDbConnect;
use PDOException;
use Tests\Mocks\GenericClass;
use Symfony\Component\Dotenv\Dotenv;

/**
 * For tests that need MySQL: the test database named in phpunit.xml (DB_DATABASE, designercms_test), reached with the
 * user and password from .env. phpunit.xml's values win over .env's, so a test never touches the app's own database.
 * The test is skipped (with the reason) only when there is nothing to test against: no database user configured, or
 * no MySQL server reachable. Anything else, such as a wrong password or a missing test database, fails the test, so a
 * broken setup can't pass by skipping. Logging goes to a
 * quiet stand-in, as in the other tests that log (the real Logger is not set up under PHPUnit).
 *
 * @package Tests\Traits
 */
trait UsesTestDatabase
{
    /**
     * Load .env (without replacing phpunit.xml's values), read config fresh, and connect; skip when that fails.
     *
     * @return PhpDbConnect
     */
    protected function connectTestDatabase(): PhpDbConnect
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (is_file($envFile)) {
            (new Dotenv())->load($envFile);
        }
        Config::reset();
        Config::set('logger.handlers', []);
        $quiet = fn() => null;
        Logger::setResource(new GenericClass(array_fill_keys(
            ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
            $quiet
        )));
        DbConnect::disconnectAll();
        if (PhpDbConnect::configuredSettings()['username'] === '') {
            $this->markTestSkipped('No database user configured (DB_USERNAME in .env)');
        }
        try {
            return PhpDbConnect::instantiateDB();
        } catch (PDOException $e) {
            // 2002: no server at that address or socket; 2006: the server went away
            if (in_array((int)$e->getCode(), [2002, 2006], true)) {
                $this->markTestSkipped('No MySQL server reachable (' . $e->getMessage() . ')');
            }
            throw $e;
        }
    }

    /**
     * Forget the connections and config after each test.
     *
     * @return void
     */
    protected function disconnectTestDatabase(): void
    {
        DbConnect::disconnectAll();
        Config::reset();
    }
}
