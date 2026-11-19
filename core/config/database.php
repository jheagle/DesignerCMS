<?php

return [
    'connection' => envGet('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'database' => envGet('DB_DATABASE', ''),
            'hostname' => envGet('DB_HOSTNAME', 'localhost'),
            'username' => envGet('DB_USERNAME', ''),
            'password' => envGet('DB_PASSWORD', ''),
        ]
    ]
];
