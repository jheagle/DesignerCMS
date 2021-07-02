<?php

namespace Core\Adaptors;

use Core\Utilities\Functional\Pure;

/**
 * Class Lang
 *
 * @package Core\Adaptors
 */
class Lang
{
    private static ?array $locales = null;
    private static ?array $translations = null;

    /**
     * Retrieve class or instance.
     *
     * @param string|null $dotNotation
     * @param array $values
     *
     * @return mixed
     */
    public static function get(string $dotNotation = null, array $values = []): mixed
    {
        if (is_null($dotNotation)) {
            return self::getTranslations();
        }
        $defaultLocale = Config::get('system.defaultLocale', 'en-ca');
        $defaultTranslation = Pure::dotGet(
            self::getTranslations(),
            "$defaultLocale.$dotNotation",
            Pure::strAfterLast($dotNotation, '.')
        );
        $locale = Config::get('system.locale', $defaultLocale);
        $result = Pure::dotGet(self::getTranslations(), "$locale.$dotNotation", $defaultTranslation);
        return is_string($result) ? sprintf($result, ...$values) : $result;
    }

    /**
     * Retrieve or rebuild the config data.
     *
     * @return array
     */
    private static function getTranslations(): array
    {
        $langPath = Config::get('system.langPath', 'core/lang');
        if (!is_null(self::$translations ?? null)) {
            return self::$translations;
        }
        self::$locales = array_map(
            fn(string $path) => Pure::strAfterLast($path, '/'),
            glob($langPath . '/*', GLOB_ONLYDIR)
        );
        self::$translations = array_reduce(
            self::$locales,
            fn(array $translations, string $locale) => array_reduce(
                scandir($langPath . "/$locale"),
                function (array $configArray, string $file) use ($langPath, $locale): array {
                    if (!preg_match('/^(.){1,2}$/', $file)) {
                        $configArray[$locale][Pure::strBeforeLast($file, '.')] = include $langPath . "/$locale/$file";
                    }
                    return $configArray;
                },
                $translations
            ),
            []
        );
        return self::$translations;
    }
}