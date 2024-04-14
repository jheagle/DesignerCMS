<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    'langPath' => envGet('LANG_PATH', __DIR__ . '/core/lang'),
    'testing' => (bool) envGet('TESTING', false),
    'production' => (bool) envGet('PRODUCTION', true),
];