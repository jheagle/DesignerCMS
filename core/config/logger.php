<?php

use Core\Adaptors\Vendor\Logger\Logger;

return [
    'channelName' => 'system',
    'handlers' => [
        [
            'type' => Logger::HANDLER_FILE_STREAM,
            'context' => [
                'path' => 'logs/all.log',
            ],
        ],
    ],
    'logLevel' => Logger::LEVEL_DEBUG,
];