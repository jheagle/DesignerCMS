<?php

namespace Tests\Unit\Core\Adaptors\Vendor;

use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Logger\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Mocks\GenericClass;
use Tests\TestCase;

/**
 * The real Logger (Monolog) under PHPUnit: it writes to the test log, whatever was called on it first.
 *
 * @package Tests\Unit\Core\Adaptors\Vendor
 */
#[CoversClass(Logger::class)]
#[Group('Unit')]
#[Group('Logger')]
class LoggerTest extends TestCase
{
    /**
     * The test log, as phpunit.xml names it.
     *
     * @return string
     */
    private function logFile(): string
    {
        return Config::get('logger.handlers')[0]['context']['stream'];
    }

    /**
     * Given the test settings
     * When reading where the Logger writes
     * Then it is the test log, not the app's, as an absolute path (the same file whatever directory PHPUnit runs from).
     */
    #[Test]
    final public function writesToTheTestLog(): void
    {
        $this->assertSame(dirname(__DIR__, 5) . '/logs/test.log', $this->logFile());
    }

    /**
     * Given a fresh Logger
     * When logging
     * Then the line is in the test log.
     */
    #[Test]
    final public function logsToTheFile(): void
    {
        $marker = 'first use ' . bin2hex(random_bytes(4));
        Logger::info($marker);
        $this->assertStringContainsString($marker, file_get_contents($this->logFile()));
    }

    /**
     * Given the Logger was reset (or initialised) before its first log line
     * When logging
     * Then the real Monolog logger is built and writes (it used to be left as a bare class name).
     */
    #[Test]
    #[DataProvider('firstCalls')]
    final public function logsAfterResetOrInitialize(string $firstCall): void
    {
        Logger::$firstCall();
        $marker = "after $firstCall " . bin2hex(random_bytes(4));
        Logger::info($marker);
        $this->assertInstanceOf(\Monolog\Logger::class, Logger::singleton()->classInstance);
        $this->assertStringContainsString($marker, file_get_contents($this->logFile()));
    }

    /**
     * The calls that cache an instance naming only the class.
     *
     * @return array
     */
    public static function firstCalls(): array
    {
        return ['reset' => ['reset'], 'initialize' => ['initialize']];
    }

    /**
     * Given a custom resource set with setResource(), even one that is a string
     * When asking for the singleton again and again
     * Then the same instance comes back, carrying that resource.
     */
    #[Test]
    #[DataProvider('customResources')]
    final public function customResourceIsKept(mixed $resource): void
    {
        Logger::setResource($resource);
        $first = Logger::singleton();
        $this->assertSame($resource, $first->classInstance);
        $this->assertSame($first, Logger::singleton());
    }

    /**
     * A stand-in object, and a string.
     *
     * @return array
     */
    public static function customResources(): array
    {
        return ['object' => [new GenericClass(['info' => fn() => null])], 'string' => ['a-string-resource']];
    }

    /**
     * Given the log file the Logger creates
     * When checking its mode
     * Then its owner can read and write it (0644; the config's '0644' string used to give 1204, unreadable).
     */
    #[Test]
    final public function logFileIsReadable(): void
    {
        Logger::info('make sure the file exists');
        $this->assertSame('644', substr(sprintf('%o', fileperms($this->logFile())), -3));
    }

    /**
     * Given several log calls (each one goes through singleton())
     * When logging one more line
     * Then it is written once (handlers used to be added on every call, so each line was written again and again).
     */
    #[Test]
    final public function eachLineIsWrittenOnce(): void
    {
        Logger::info('one');
        Logger::info('two');
        Logger::info('three');
        $marker = 'once ' . bin2hex(random_bytes(4));
        Logger::info($marker);
        $this->assertSame(1, substr_count(file_get_contents($this->logFile()), $marker));
        $this->assertCount(1, Logger::singleton()->classInstance->getHandlers());
    }
}
