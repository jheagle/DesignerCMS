<?php

return [
    'defaultLocale' => defaultValue('en-ca', envGet('DEFAULT_LANG')),
    'langPath' => defaultValue(__DIR__ . '/core/lang', envGet('LANG_PATH')),
    'testing' => defaultValue(false, envGet('TESTING')),
    'production' => defaultValue(true, envGet('PRODUCTION')),
];