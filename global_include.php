<?php

$localHosts = ['127.0.0.1', '::1'];
if (in_array($_SERVER['SERVER_ADDR'], $localHosts, true) && in_array(
    $_SERVER['REMOTE_ADDR'],
    $localHosts,
    true
  )) {
    //    header('Content-Type: application/json'); This is my debuggin trick, but if I have xdebug then this ruins it
    $testing = true;
    $production = false;
}

$HOME = '';

$globalDirs = [
  'account',
  'admin',
  'controllers',
  'img',
  'js',
  'models',
  'sudo',
  'resources',
  'css',
  'models/core',
  'models/database',
  'models/entity',
  'models/utilities',
];

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

require_once $CORE['core'];

function dirAssocArray($directory, $path, $reset = false)
{
    static $assocArray = [];
    if (!isset($assocArray[$directory]) || $reset) {
        $dirArray = scandir($path);
        $assocArray[$directory] = [];
        foreach ($dirArray as $file) {
            if (preg_match('/^(.){1,2}$/', $file)) {
                continue;
            }
            $parts = explode('.', $file);
            unset($parts[count($parts) - 1]);
            $string = implode('.', $parts);
            $assocArray[$directory][$string] = $path.$file;
        }
    }

    return $assocArray[$directory];
}
