<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    // This file is in core/config, so the translations are one folder up (core/lang)
    'langPath' => envGet('LANG_PATH', dirname(__DIR__) . '/lang'),
    // .env values are strings, and (bool) 'false' is true: read 'false', '0', 'off', 'no' and '' as false
    'testing' => filter_var(envGet('TESTING', false), FILTER_VALIDATE_BOOLEAN),
    'production' => filter_var(envGet('PRODUCTION', true), FILTER_VALIDATE_BOOLEAN),
    // Where php perform models writes the models generated from the database (core/config is two folders down)
    'modelsPath' => envGet('MODELS_PATH', dirname(__DIR__, 2) . '/models'),
    'modelsNamespace' => envGet('MODELS_NAMESPACE', 'Models'),
];