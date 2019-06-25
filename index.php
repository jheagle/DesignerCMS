<?php
require 'vendor/autoload.php';

use Core\Database\PhpDbConnect;
use Core\DataTypes\Numbers\IntDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Field;
use Core\Utilities\Functional\Pure;

Pure::extractFunctions();
function curryTest($one, $two, $three):string
{
    return "$one-$two-$three";
}
trace()(curryTest('one', 'two', 'three'));
$newCurry1 = Pure::curry('curryTest')('one');
trace()($newCurry1);
$newCurry2 = $newCurry1('two');
trace()($newCurry2);
trace()($newCurry2('three'));


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

if (!isset($username)) {
    $username = 'root';
}

if (!isset($hostname)) {
    $hostname = 'localhost';
}

$db = PHPDBConnect::instantiateDB('', '', '', '', $testing, $production);

$value = 'Hello';
$datatype = new VarCharDt($value, ['length' => 100]);
trace()($datatype);
trace()($datatype->getValue());

$value = 128;
$testValue = 32;
$integer = new IntDt($value, ['length' => 0, 'isSigned' => false]);
trace()($integer);
trace()($integer->getValue());
trace()($testValue);
trace()($integer->isEven());
trace()($integer->getAbsolute());
trace()($integer->isEqual('4294967295'));
echo 'Bitwise Add: ' . $integer->add($testValue) . "\n<br>";
echo 'True Add: ' . ($integer->getValue() + $testValue) . "\n<br>";
echo 'Bitwise Subtract: ' . $integer->subtract($testValue) . "\n<br>";
echo 'True Subtract: ' . ($integer->getValue() - $testValue) . "\n<br>";
echo 'Bitwise Mulitiply: ' . $integer->multiplyBy($testValue) . "\n<br>";
echo 'True Multiple: ' . ($integer->getValue() * $testValue) . "\n<br>";
echo 'Bitwise Divide: ' . $integer->divideBy($testValue) . "\n<br>";
echo 'True Divide: ' . ($integer->getValue() / $testValue) . "\n<br>";
echo 'Bitwise Modulo: ' . $integer->modulo($testValue) . "\n<br>";
echo 'True Modulo: ' . ($integer->getValue() % $testValue) . "\n<br>";

$field = new Field('column', 'BigInt', 0, 50, Field::ZERO_FILL | Field::UNSIGNED);
$field->setValue('999999999999999999');
trace()($field);
trace()($field->getValue());
