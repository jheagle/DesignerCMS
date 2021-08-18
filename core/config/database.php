<?php

return [
    'connection' => defaultValue('mysql', envGet('DB_CONNECTION')),

    'connections' => [
        'mysql' => [
            'database' => defaultValue('127.0.0.1', envGet('DB_DATABASE')),
            'hostname' => defaultValue('localhost', envGet('DB_HOSTNAME')),
            'username' => defaultValue('root', envGet('DB_USERNAME')),
            'password' => defaultValue('', envGet('DB_PASSWORD')),
        ]
    ]
];
