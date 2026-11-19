<?php

use Core\Adaptors\Vendor\Logger\Logger;

return [
    'channelName' => 'system',
    'handlers' => [
        [
            'type' => Logger::HANDLER_FILE_STREAM,
            'context' => [
                'stream' => envGet('LOG_FILE', 'logs/all.log'),
                // An octal number: the string '0644' became decimal 644 (octal 1204), a file its owner couldn't read
                'filePermission' => 0644
            ],
        ],
    ],
    'logLevel' => Logger::LEVEL_DEBUG,
];