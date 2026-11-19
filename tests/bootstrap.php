<?php

/**
 * PHPUnit's bootstrap: the autoloader, then an empty test log (phpunit.xml points LOG_FILE at it), so each run's log
 * holds only that run and nothing builds up in logs/all.log.
 */
require __DIR__ . '/../vendor/autoload.php';

$testLog = __DIR__ . '/../logs/test.log';
if (is_file($testLog)) {
    file_put_contents($testLog, '');
}
