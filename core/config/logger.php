<?php

use Core\Adaptors\Vendor\Logger\Logger;

return [
    'channelName' => 'system',
    'handlers' => [
        [
            'type' => Logger::HANDLER_FILE_STREAM,
            'context' => [
                'stream' => defaultValue('logs/all.log', envGet('LOG_FILE')),
                'filePermission' => '0644'
            ],
        ],
    ],
    'logLevel' => Logger::LEVEL_DEBUG,
];