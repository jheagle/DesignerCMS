<?php

require __DIR__ . '/vendor/autoload.php';

use Core\Adaptors\Vendor\OAuth\Provider;
use Core\DataTypes\Numbers\Integers\IntDt;
use Core\DataTypes\Strings\VarCharDt;
use Core\Entity\Field;
use Core\Utilities\Functional\Pure;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

VarDumper::setHandler(function ($var) {
    $htmlDumper = new HtmlDumper();
    $htmlDumper->setStyles([
        'default' => 'background-color:transparent; color:#FF1B00D4; line-height:1.2em; font:14px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-all',
        'num' => 'font-weight:bold; color:#1231DA',
        'const' => 'font-weight:bold',
        'str' => 'font-weight:bold; color:#13B125',
        'note' => 'color:#1231DA',
        'ref' => 'color:#A0A0A0',
        'public' => 'color:#222',
        'protected' => 'color:#222',
        'private' => 'color:#222',
        'meta' => 'color:#B729D9',
        'key' => 'color:#13B125',
        'index' => 'color:#1231DA',
        'ellipsis' => 'color:#FF1B00D4',
    ]);

    $dumper = PHP_SAPI === 'cli' ? new CliDumper() : $htmlDumper;

    $dumper->dump((new VarCloner())->cloneVar($var));
});

Pure::extractAll();

$providerTest = Provider::instantiate();
echo toHtml(string: $providerTest);
function curryTest($one, $two, $three): string
{
    return "$one-$two-$three";
}

trace('curryTest with all parameters')(curryTest('one', 'two', 'three'));
$newCurry1 = Pure::curry('curryTest')('one');
trace('curryTest with one parameter')($newCurry1);
$newCurry2 = $newCurry1('two');
trace('curryTest with two parameters')($newCurry2);
trace('curryTest with all three parameters')($newCurry2('three'));

require __DIR__ . '/bootstrap.php';

$value = 'Hello';
$datatype = new VarCharDt($value, ['length' => 100]);
trace('VarChar')($datatype);
trace('VarChar: getValue')($datatype->getValue());

$value = 128;
$testValue = 32;
$integer = new IntDt($value, ['length' => 0, 'isSigned' => false]);
trace('Integer')($integer);
trace('Integer: getValue')($integer->getValue());
trace('Number 32')($testValue);
trace('Integer: isEven')($integer->isEven());
trace('Integer: getAbsolute')($integer->getAbsolute());
trace('Integer: isEqual to 4294967295')($integer->isEqual('4294967295'));
trace('Bitwise Add:')($integer->add($testValue));
trace('True Add:')($integer->getValue() + $testValue);
trace('Bitwise Subtract:')($integer->subtract($testValue));
trace('True Subtract:')($integer->getValue() - $testValue);
trace('Bitwise Multiply:')($integer->multiplyBy($testValue));
trace('True Multiple:')($integer->getValue() * $testValue);
trace('Bitwise Divide:')($integer->divideBy($testValue));
trace('True Divide:')($integer->getValue() / $testValue);
trace('Bitwise Modulo:')($integer->modulo($testValue));
trace('True Modulo:')($integer->getValue() % $testValue);

$field = new Field('column', 'BigInt', 0, 50, Field::ZERO_FILL | Field::UNSIGNED);
$field->setValue('999999999999999999');
trace('Field')($field);
trace('Field: getValue')($field->getValue());
