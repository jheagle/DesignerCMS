<?php

return [
    'connection' => envGet('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'database' => envGet('DB_DATABASE', '127.0.0.1'),
            'hostname' => envGet('DB_HOSTNAME', 'localhost'),
            'username' => envGet('DB_USERNAME', 'root'),
            'password' => envGet('DB_PASSWORD', ''),
        ]
    ]
];
