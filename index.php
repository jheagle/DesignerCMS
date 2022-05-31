<?php

require __DIR__ . '/vendor/autoload.php';
use Core\Adaptors\Vendor\OAuth\Provider;
use Core\DataTypes\Numbers\Integers\BigIntDt;
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

function curryTest($one, $two, $three): string
{
    return "$one-$two-$three";
}

dump('curryTest with all parameters', curryTest('one', 'two', 'three'));
$newCurry1 = Pure::curry('curryTest')('one');
dump('curryTest with one parameter', $newCurry1);
$newCurry2 = $newCurry1('two');
dump('curryTest with two parameters', $newCurry2);
dump('curryTest with all three parameters', $newCurry2('three'));

require __DIR__ . '/bootstrap.php';

$value = 'Hello';
$datatype = new VarCharDt($value, ['length' => 100]);
dump('VarChar', $datatype);
dump('VarChar: getValue', $datatype->getValue());

$value = 128;
$testValue = 32;
$integer = new IntDt($value, ['length' => 0, 'isSigned' => false]);
dump('Integer', $integer);
dump('Integer: getValue', $integer->getValue());
dump('Number 32', $testValue);
dump('Integer: isEven', $integer->isEven());
dump('Integer: getAbsolute', $integer->getAbsolute());
dump('Integer: isEqual to 4294967295', $integer->isEqual('4294967295'));
dump('Bitwise Add:', $integer->add($testValue));
dump('True Add:', $integer->getValue() + $testValue);
dump('Bitwise Subtract:', $integer->subtract($testValue));
dump('True Subtract:', $integer->getValue() - $testValue);
dump('Bitwise Multiply:', $integer->multiplyBy($testValue));
dump('True Multiple:', $integer->getValue() * $testValue);
dump('Bitwise Divide:', $integer->divideBy($testValue));
dump('True Divide:', $integer->getValue() / $testValue);
dump('Bitwise Modulo:', $integer->modulo($testValue));
dump('True Modulo:', $integer->getValue() % $testValue);

$field = new Field('column', BigIntDt::class, 0, 50, Field::ZERO_FILL | Field::UNSIGNED);
$field->setValue('999999999999999999');
dump('Field', $field);
dump('Field: getValue', $field->getValue());

$providerTest = Provider::instantiate();
echo toHtml(string: $providerTest);
