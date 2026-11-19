<?php

/**
 * PHPUnit's bootstrap: the autoloader, then a fresh test log. phpunit.xml names the log relative to the project
 * (LOG_FILE=logs/test.log); it is made absolute here, so the Logger and this file agree on it whatever directory
 * PHPUnit runs from. The old file is deleted rather than emptied, so the Logger creates it again with the configured
 * mode, and each run's log holds only that run.
 */
require __DIR__ . '/../vendor/autoload.php';

$projectRoot = dirname(__DIR__);
$testLog = $_ENV['LOG_FILE'] ?? 'logs/test.log';
if (!str_starts_with($testLog, '/')) {
    $testLog = "$projectRoot/$testLog";
}
$_ENV['LOG_FILE'] = $testLog;
putenv("LOG_FILE=$testLog");
if (is_file($testLog)) {
    unlink($testLog);
}
