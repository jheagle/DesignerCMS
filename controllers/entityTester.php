<?php

header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');
require_once($MODELS['phpDBConnect']);
require_once($MODELS['EntityClass']);

$db = PHPDBConnect::instantiateDB('', '', '', '', true, false);

