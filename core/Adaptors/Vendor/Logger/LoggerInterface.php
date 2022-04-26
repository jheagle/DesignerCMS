<?php

namespace Core\Adaptors\Vendor\Logger;

interface LoggerInterface
{
    /**
     * Log type alert.
     *
     * @param string $message
     * @param array $context
     */
    public static function alert(string $message, array $context = []): void;

    /**
     * Log type critical.
     *
     * @param string $message
     * @param array $context
     */
    public static function critical(string $message, array $context = []): void;

    /**
     * Log type debug.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void;

    /**
     * Log type emergency.
     *
     * @param string $message
     * @param array $context
     */
    public static function emergency(string $message, array $context = []): void;

    /**
     * Log type error.
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void;

    /**
     * Log type info.
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void;

    /**
     * Log type notice.
     *
     * @param string $message
     * @param array $context
     */
    public static function notice(string $message, array $context = []): void;

    /**
     * Log type warning.
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void;

    /**
     * Create a single instance of this class.
     *
     * @param mixed|null $resource
     * @param mixed ...$args
     *
     * @return static
     */
    public static function singleton(mixed $resource = null, ...$args): static;
}