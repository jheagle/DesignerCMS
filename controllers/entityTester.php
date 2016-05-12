<?php

if (!isset($ROOT)) {
    $ROOT = dirname(__DIR__);
}
require_once $ROOT.'/global_include.php';
require_once $MODELS['phpDBConnect'];
require_once $MODELS['EntityClass'];

$db = PHPDBConnect::instantiateDB('', '', '', '', $testing, $production);
