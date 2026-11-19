<?php

return [
    'defaultLocale' => envGet('DEFAULT_LANG', 'en-ca'),
    // This file is in core/config, so the translations are one folder up (core/lang)
    'langPath' => envGet('LANG_PATH', dirname(__DIR__) . '/lang'),
    'testing' => (bool) envGet('TESTING', false),
    'production' => (bool) envGet('PRODUCTION', true),
];