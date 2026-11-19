<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    'langPath' => envGet('LANG_PATH', __DIR__ . '/core/lang'),
    // .env values are strings, and (bool) 'false' is true: read 'false', '0', 'off', 'no' and '' as false
    'testing' => filter_var(envGet('TESTING', false), FILTER_VALIDATE_BOOLEAN),
    'production' => filter_var(envGet('PRODUCTION', true), FILTER_VALIDATE_BOOLEAN),
];