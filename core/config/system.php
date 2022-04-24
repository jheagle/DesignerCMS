<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    'langPath' => envGet('LANG_PATH', __DIR__ . '/core/lang'),
    'testing' => envGet('TESTING', false),
    'production' => envGet('PRODUCTION', true),
];