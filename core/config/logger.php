<?php

use Core\Adaptors\Vendor\Logger\Logger;

return [
    'channelName' => 'system',
    'handlers' => [
        [
            'type' => Logger::HANDLER_FILE_STREAM,
            'context' => [
                'stream' => envGet('LOG_FILE', 'logs/all.log'),
                'filePermission' => '0644'
            ],
        ],
    ],
    'logLevel' => Logger::LEVEL_DEBUG,
];