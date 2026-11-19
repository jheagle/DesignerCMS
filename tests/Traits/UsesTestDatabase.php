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
 * When no test database can be reached (no .env, MySQL down), the test is skipped with the reason. Logging goes to a
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
        try {
            return PhpDbConnect::instantiateDB();
        } catch (PDOException $e) {
            $this->markTestSkipped('No MySQL test database (' . $e->getMessage() . ')');
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
