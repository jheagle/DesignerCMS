<?php

if ($_SERVER['SERVER_ADDR'] === '127.0.0.1' && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    header('Content-Type: application/json');
    $testing = true;
    $production = false;
}

$HOME = '';

$globalDirs = array('account', 'admin', 'controllers', 'img', 'js', 'models', 'sudo', 'resources', 'css');

if (empty($ROOT)) {
    $ROOT = __DIR__;
}

if (!isset($reset)) {
    $reset = false;
}

foreach ($globalDirs as $dir) {
    $dirParts = explode('/', $dir);
    $uppDir = strtoupper($dirParts[count($dirParts) - 1]);
    $$uppDir = dirAssocArray($uppDir, "{$ROOT}/{$dir}/", $reset);
}
unset($globalDirs, $dirParts, $dir, $uppDir, $reset);

require_once $MODELS['core'];

function dirAssocArray($directory, $path, $reset = false) {
    static $assocArray = array();
    if (!isset($assocArray[$directory]) || $reset) {
        $dirArray = scandir($path);
        $assocArray[$directory] = array();
        foreach ($dirArray as $file) {
            if (preg_match('/^(.){1,2}$/', $file)) {
                continue;
            }
            $parts = explode('.', $file);
            unset($parts[count($parts) - 1]);
            $string = implode('.', $parts);
            $assocArray[$directory][$string] = $path . $file;
        }
    }

    return $assocArray[$directory];
}
