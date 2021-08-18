<?php

use Core\Adaptors\Vendor\Logger\Logger;

return [
    'channelName' => 'system',
    'handlers' => [
        [
            'type' => Logger::HANDLER_FILE_STREAM,
            'context' => [
                'stream' => 'logs/all.log',
                'filePermission' => '0644'
            ],
        ],
    ],
    'logLevel' => Logger::LEVEL_DEBUG,
];