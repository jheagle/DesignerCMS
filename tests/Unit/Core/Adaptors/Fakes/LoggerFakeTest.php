<?php

namespace Tests\Unit\Core\Adaptors\Fakes;

use Core\Adaptors\Config;
use Core\Adaptors\Fakes\LoggerFake;
use Core\Adaptors\SharedState;
use Core\Adaptors\Vendor\Logger\Logger;
use LogicException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Logger::fake(): log lines are recorded instead of written, and checked by level and text.
 *
 * @package Tests\Unit\Core\Adaptors\Fakes
 */
#[CoversClass(LoggerFake::class)]
#[Group('Unit')]
#[Group('Logger')]
class LoggerFakeTest extends TestCase
{
    /**
     * Given a faked Logger
     * When lines are logged
     * Then they can be checked by level and text, and none reach the log file.
     */
    #[Test]
    final public function checksLoggedLines(): void
    {
        $logFile = Config::get('logger.handlers')[0]['context']['stream'];
        $sizeBefore = is_file($logFile) ? filesize($logFile) : 0;
        Logger::fake();
        Logger::error('Unable to connect to shop@localhost');
        Logger::info('Connected');
        Logger::assertLogged('error', 'Unable to connect');
        Logger::assertLogged('info');
        Logger::assertNotLogged('error', 'Connected');
        Logger::assertNotLogged('warning');
        clearstatcache();
        $this->assertSame($sizeBefore, is_file($logFile) ? filesize($logFile) : 0);
    }

    /**
     * Given a faked Logger with nothing logged
     * When checking
     * Then assertNothingLogged passes, and assertLogged fails saying nothing was logged.
     */
    #[Test]
    final public function nothingLogged(): void
    {
        Logger::fake();
        Logger::assertNothingLogged();
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected a line logged at error containing "boom"; nothing was logged');
        Logger::assertLogged('error', 'boom');
    }

    /**
     * Given a line that doesn't match
     * When checking for another
     * Then the failure lists what was logged.
     */
    #[Test]
    final public function failureListsWhatWasLogged(): void
    {
        Logger::fake();
        Logger::info('Connected');
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('logged: info: Connected');
        Logger::assertLogged('error');
    }

    /**
     * Given no fake
     * When checking what was logged
     * Then it says to call Logger::fake() first.
     */
    #[Test]
    final public function checkingWithoutAFakeExplains(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Call Logger::fake() before checking what was logged');
        Logger::assertLogged('error');
    }

    /**
     * Given a faked Logger
     * When shared state is reset (as between tests)
     * Then the real Logger is back.
     */
    #[Test]
    final public function resetEndsTheFake(): void
    {
        Logger::fake();
        SharedState::reset();
        $this->assertInstanceOf(\Monolog\Logger::class, Logger::singleton()->classInstance);
    }

    /**
     * Given a log level that would drop info lines
     * When the Logger is faked
     * Then every line is recorded anyway, so the checks see what the code logged, not what the config kept.
     */
    #[Test]
    final public function fakeRecordsEveryLevel(): void
    {
        Config::set('logger.logLevel', Logger::LEVEL_ERROR);
        Logger::fake();
        Logger::info('below the configured level');
        Logger::assertLogged('info', 'below the configured level');
    }

    /**
     * Given a line that was logged
     * When checking assertNotLogged for it
     * Then it fails, listing what was logged.
     */
    #[Test]
    final public function failedNotLoggedListsWhatWasLogged(): void
    {
        Logger::fake();
        Logger::error('Unable to connect');
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected no line logged at error containing "Unable"; logged: error: Unable to connect');
        Logger::assertNotLogged('error', 'Unable');
    }

    /**
     * Given a call on the fake with no arguments
     * When a check fails and lists what was logged
     * Then the listing copes (it read the first argument of every call).
     */
    #[Test]
    final public function listingCopesWithCallsWithoutArguments(): void
    {
        $fake = Logger::fake();
        $fake->reset();
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('logged: reset: ');
        $fake->assertLogged('error');
    }
}
