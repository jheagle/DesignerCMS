<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    // This file is in core/config, so the translations are one folder up (core/lang)
    'langPath' => envGet('LANG_PATH', dirname(__DIR__) . '/lang'),
    // .env values are strings, and (bool) 'false' is true: read 'false', '0', 'off', 'no' and '' as false
    'testing' => filter_var(envGet('TESTING', false), FILTER_VALIDATE_BOOLEAN),
    'production' => filter_var(envGet('PRODUCTION', true), FILTER_VALIDATE_BOOLEAN),
    // Where php perform models writes the models generated from the database: a relative MODELS_PATH is from the
    // project folder (core/config is two folders down)
    'modelsPath' => (static function (string $path): string {
        return str_starts_with($path, '/') ? $path : dirname(__DIR__, 2) . '/' . $path;
    })(envGet('MODELS_PATH', '') ?: 'models'),
    'modelsNamespace' => envGet('MODELS_NAMESPACE', 'Models'),
];