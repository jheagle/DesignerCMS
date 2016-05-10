<?php

$HOME = '';

$globalDirs = array('account', 'admin', 'controllers', 'img', 'js', 'models', 'sudo', 'resources', 'css');

if (!isset($reset)) {
    $reset = false;
}

foreach ($globalDirs as $dir) {
    $dirParts = explode('/', $dir);
    $uppDir = strtoupper($dirParts[count($dirParts) - 1]);
    $$uppDir = dirAssocArray($uppDir, "{$_SERVER['DOCUMENT_ROOT']}/{$dir}/", $reset);
}
unset($globalDirs, $dirParts, $dir, $uppDir, $reset);

function dirAssocArray($directory, $path, $reset = false)
{
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
            $assocArray[$directory][$string] = $path.$file;
        }
    }

    return $assocArray[$directory];
}
