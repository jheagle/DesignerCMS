<?php

namespace Core\Adaptors;

use Core\Utilities\Functional\Pure;

/**
 * Class Config
 *
 * @package Core\Adaptors
 */
class Config
{
    private static ?array $config = null;
    private static string $configPath = 'core/config';

    /**
     * Retrieve class or instance.
     *
     * @param null|string $dotNotation
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(string $dotNotation = null, mixed $default = null): mixed
    {
        if (is_null($dotNotation)) {
            return self::getConfig();
        }
        return Pure::dotGet(self::getConfig(), $dotNotation, $default);
    }

    /**
     * Assign a resource to an adapter classname.
     *
     * @param string $dotNotation
     * @param object|string $resource
     *
     * @return mixed
     */
    public static function set(string $dotNotation, mixed $resource): mixed
    {
        self::$config = Pure::dotSet(self::getConfig(), $dotNotation, $resource);
        return $resource;
    }

    /**
     * Clear the config data, optionally provide custom config.
     *
     * @param array|null $configOverride
     *
     * @return array
     */
    public static function reset(array $configOverride = null): array
    {
        self::$config = $configOverride;
        return self::$config ?? [];
    }

    /**
     * Retrieve or rebuild the config data.
     *
     * @return array
     */
    private static function getConfig(): array
    {
        if (!is_null(self::$config ?? null)) {
            return self::$config;
        }
        self::$config = array_reduce(
            scandir(self::$configPath),
            function (array $configArray, string $file): array {
                if (!preg_match('/^(.){1,2}$/', $file)) {
                    $configArray[Pure::strBeforeLast($file, '.')] = include self::$configPath . "/$file";
                }
                return $configArray;
            },
            []
        );
        return self::$config;
    }
}