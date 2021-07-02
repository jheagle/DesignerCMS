<?php

namespace Core\Adaptors\Vendor\Logger;

use Core\Adaptors\Adaptor;
use Core\Adaptors\Config;
use Core\Adaptors\Vendor\Logger\Handlers\StreamHandler;
use Core\Utilities\Functional\Pure;

/**
 * Class Logger
 *
 * @package Core\Adaptors\Vendor\Logger
 *
 * @property \Monolog\Logger $classInstance
 */
class Logger extends Adaptor
{
    public const HANDLER_FILE_STREAM = 'file_stream';

    public const HANDLERS = [
        self::HANDLER_FILE_STREAM => StreamHandler::class,
    ];

    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_INFO = 'info';
    public const LEVEL_NOTICE = 'notice';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_ALERT = 'alert';
    public const LEVEL_CRITICAL = 'critical';
    public const LEVEL_EMERGENCY = 'emergency';
    public const LEVELS = [
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
        foreach (Config::get('logger.handlers', []) as $handler) {
            /**
             * @var Adaptor $handlerClass
             */
            $handlerClass = self::HANDLERS[$handler['type']];
            $newInstance->classInstance->pushHandler(
                $handlerClass::instantiate()
                    ->with(array_values(Pure::dotGet($handler, 'context')))
                    ->build()
                    ->preCast()
            );
        }
        return $newInstance;
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
        return array_search($level, self::LEVELS) < array_search(
                Config::get('logger.logLevel', self::LEVEL_ERROR),
                self::LEVELS
            );
    }
}