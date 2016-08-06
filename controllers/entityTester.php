<?php

if (!isset($ROOT)) {
    $ROOT = dirname(__DIR__);
}
require_once $ROOT . '/global_include.php';
require_once $MODELS['phpDBConnect'];
require_once $MODELS['EntityClass'];

$db = PHPDBConnect::instantiateDB('', '', '', '', $testing, $production);

$value = 'Hello';
$datatype = new VarChar($value, 100);
var_dump($datatype);
var_dump($datatype->getValue());

$value = 128;
$testValue = 32;
$integer = new Int($value, false);
var_dump($integer);
var_dump($integer->getValue());
var_dump($testValue);
var_dump($integer->isEven());
var_dump($integer->getAbsolute());
var_dump($integer->isEqual('4294967295'));
echo 'Bitwise Add: ' . $integer->add($testValue) . "\n";
echo 'True Add: ' . ($integer->getValue() + $testValue) . "\n";
echo 'Bitwise Subtract: ' . $integer->subtract($testValue) . "\n";
echo 'True Subtract: ' . ($integer->getValue() - $testValue) . "\n";
echo 'Bitwise Mulitiply: ' . $integer->multiplyBy($testValue) . "\n";
echo 'True Multiple: ' . ($integer->getValue() * $testValue) . "\n";
echo 'Bitwise Divide: ' . $integer->divideBy($testValue) . "\n";
echo 'True Divide: ' . ($integer->getValue() / $testValue) . "\n";
echo 'Bitwise Modulo: ' . $integer->modulo($testValue) . "\n";
echo 'True Modulo: ' . ($integer->getValue() % $testValue) . "\n";

$field = new Field('column', 'BigInt', 0, 50, Field::ZERO_FILL | Field::UNSIGNED);
$field->setValue('999999999999999999');
var_dump($field);
var_dump($field->getValue());
