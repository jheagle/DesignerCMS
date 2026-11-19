<?php

namespace Core\Adaptors\Vendor\Logger;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Config;
use Core\Adaptors\Fakes\LoggerFake;
use Core\Adaptors\Vendor\Logger\Handlers\StreamHandler;
use Core\Utilities\Functional\Pure;
use LogicException;

/**
 * Class Logger
 *
 * @package Core\Adaptors\Vendor\Logger
 *
 * @property \Monolog\Logger $classInstance
 */
class Logger extends Adaptor implements LoggerInterface
{
    public const string HANDLER_FILE_STREAM = 'file_stream';

    public const array HANDLERS = [
        self::HANDLER_FILE_STREAM => StreamHandler::class,
    ];

    public const string LEVEL_DEBUG = 'debug';
    public const string LEVEL_INFO = 'info';
    public const string LEVEL_NOTICE = 'notice';
    public const string LEVEL_WARNING = 'warning';
    public const string LEVEL_ERROR = 'error';
    public const string LEVEL_ALERT = 'alert';
    public const string LEVEL_CRITICAL = 'critical';
    public const string LEVEL_EMERGENCY = 'emergency';
    public const array LEVELS = [
        self::LEVEL_DEBUG,
        self::LEVEL_INFO,
        self::LEVEL_NOTICE,
        self::LEVEL_WARNING,
        self::LEVEL_ERROR,
        self::LEVEL_ALERT,
        self::LEVEL_CRITICAL,
        self::LEVEL_EMERGENCY,
    ];

    /**
     * Log type alert.
     *
     * @param string $message
     * @param array $context
     */
    public static function alert(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_ALERT)) {
            return;
        }
        self::singleton()->classInstance->alert($message, $context);
    }

    /**
     * Log type critical.
     *
     * @param string $message
     * @param array $context
     */
    public static function critical(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_CRITICAL)) {
            return;
        }
        self::singleton()->classInstance->critical($message, $context);
    }

    /**
     * Log type debug.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_DEBUG)) {
            return;
        }
        self::singleton()->classInstance->debug($message, $context);
    }

    /**
     * Log type emergency.
     *
     * @param string $message
     * @param array $context
     */
    public static function emergency(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_EMERGENCY)) {
            return;
        }
        self::singleton()->classInstance->emergency($message, $context);
    }

    /**
     * Log type error.
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_ERROR)) {
            return;
        }
        self::singleton()->classInstance->error($message, $context);
    }

    /**
     * Log type info.
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_INFO)) {
            return;
        }
        self::singleton()->classInstance->info($message, $context);
    }

    /**
     * Log type notice.
     *
     * @param string $message
     * @param array $context
     */
    public static function notice(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_NOTICE)) {
            return;
        }
        self::singleton()->classInstance->notice($message, $context);
    }

    /**
     * Log type warning.
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void
    {
        if (self::ignoreLevel(self::LEVEL_WARNING)) {
            return;
        }
        self::singleton()->classInstance->warning($message, $context);
    }

    /**
     * Create a single instance of this class.
     *
     * @param mixed|null $resource
     * @param mixed ...$args
     *
     * @return static
     */
    public static function singleton(mixed $resource = null, ...$args): static
    {
        if (!array_key_exists('name', $args)) {
            $args['name'] = Config::get('logger.channelName', '');
        }
        $newInstance = parent::singleton($resource, ...$args);
        // Only a real Monolog logger takes handlers, and only once: this runs on every log call
        $logger = $newInstance->classInstance;
        if (!$logger instanceof \Monolog\Logger || $logger->getHandlers() !== []) {
            return $newInstance;
        }
        foreach (Config::get('logger.handlers', []) as $handler) {
            $handlerClass = self::HANDLERS[$handler['type']];
            assert($handlerClass instanceof Adaptor);
            $newInstance->classInstance->pushHandler(
                $handlerClass::instantiate()
                    ->with(Pure::dotGet($handler, 'context'))
                    ->build()
                    ->preCast()
            );
        }
        return $newInstance;
    }

    /**
     * Swap in a fake logger that records each line instead of writing it, at every level (logger.logLevel is not
     * applied while faked). Check it with Logger::assertLogged() or on the returned fake.
     *
     * @param array<string, mixed> $returns
     *
     * @return LoggerFake
     */
    public static function fake(array $returns = []): LoggerFake
    {
        $fake = new LoggerFake($returns);
        static::setResource($fake);

        return $fake;
    }

    /**
     * Check a line was logged at a level, its text containing $contains. Needs Logger::fake() first.
     *
     * @param string $level e.g. Logger::LEVEL_ERROR ('error').
     * @param string $contains
     *
     * @return LoggerFake
     */
    public static function assertLogged(string $level, string $contains = ''): LoggerFake
    {
        return self::currentFake()->assertLogged($level, $contains);
    }

    /**
     * Check no line was logged at a level containing $contains. Needs Logger::fake() first.
     *
     * @param string $level
     * @param string $contains
     *
     * @return LoggerFake
     */
    public static function assertNotLogged(string $level, string $contains = ''): LoggerFake
    {
        return self::currentFake()->assertNotLogged($level, $contains);
    }

    /**
     * Check nothing was logged. Needs Logger::fake() first.
     *
     * @return LoggerFake
     */
    public static function assertNothingLogged(): LoggerFake
    {
        return self::currentFake()->assertNothingLogged();
    }

    /**
     * The fake in use.
     *
     * @return LoggerFake
     *
     * @throws LogicException When Logger::fake() wasn't called.
     */
    private static function currentFake(): LoggerFake
    {
        $logger = self::singleton()->classInstance;
        if (!$logger instanceof LoggerFake) {
            throw new LogicException('Call Logger::fake() before checking what was logged');
        }

        return $logger;
    }

    /**
     * Check if the current type of log should be ignored and skipped.
     *
     * @param string $level
     *
     * @return bool
     */
    private static function ignoreLevel(string $level): bool
    {
        // A fake records every line the code logs, whatever the configured level, so tests check the code, not the
        // config (and assertNotLogged can't pass just because the level filtered a line out)
        if ((self::$customInstances[self::class] ?? null) instanceof LoggerFake) {
            return false;
        }
        return array_search($level, self::LEVELS) < array_search(
                Config::get('logger.logLevel', self::LEVEL_ERROR),
                self::LEVELS
            );
    }
}