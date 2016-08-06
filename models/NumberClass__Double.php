<?php

if (!class_exists('DataType')) {
    $currentFile = basename(__FILE__, '.php');
    exit("Core 'DataType' Undefined. '{$currentFile}' must not be called directly.");
}
