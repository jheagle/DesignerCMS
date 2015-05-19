<?php

$HOME = $_SERVER['DOCUMENT_ROOT'];

$globalDirs = array('account', 'admin', 'controllers', 'img', 'js', 'models', 'sudo', 'resources', 'css');

foreach ($globalDirs as $dir) {
    $dirParts = explode('/', $dir);
    $uppDir = strtoupper($dirParts[count($dirParts) - 1]);
    $$uppDir = dirAssocArray("{$HOME}/{$dir}/");
}
unset($globalDirs, $dirParts, $dir, $uppDir);

function dirAssocArray($dirName) {
    $dirArray = scandir($dirName);
    $assocArray = array();
    foreach ($dirArray as $name) {
        if (preg_match('/^(.){1,2}$/', $name)) {
            continue;
        }
        $parts = explode('.', $name);
        unset($parts[count($parts) - 1]);
        $string = implode('.', $parts);
        $assocArray[$string] = $dirName . $name;
    }
    return $assocArray;
}
