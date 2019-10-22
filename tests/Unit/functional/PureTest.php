<?php

namespace Core\Tests\Unit\Functional;

use Core\Tests\TestCase;
use Core\Utilities\Functional\Pure;

/**
 * Class PureTest
 *
 * @package Core\Tests\Unit\Functional
 *
 * @group Unit
 * @group Functional
 * @group Pure
 */
class PureTest extends TestCase
{

    /**
     * Given four string altering functions and a blank string variable
     * When passing any number of the functions to pipe in any order
     * Then pipe returns a callable function which will pass a parameter to all of the provided functions
     *
     * @test
     */
    public function pipeCanReceiveMultipleFunctions()
    {
        // create new test functions to pass to pipe
        $appendOne = function (string $str): string {
            $appendStr = 'one';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $appendTwo = function (string $str): string {
            $appendStr = 'two';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $appendThree = function (string $str): string {
            $appendStr = 'three';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $strParam = '';

        $oneCallable = Pure::pipe($appendOne);
        $this->assertIsCallable($oneCallable);
        $this->assertEquals('one', $oneCallable($strParam));
        $twoCallable = Pure::pipe($appendOne, $appendTwo);
        $this->assertIsCallable($twoCallable);
        $this->assertEquals('one-two', $twoCallable($strParam));
        $threeCallable = Pure::pipe($appendOne, $appendTwo, $appendThree);
        $this->assertIsCallable($threeCallable);
        $this->assertEquals('one-two-three', $threeCallable($strParam));
    }

    /**
     * Given four string altering functions and a blank string variable
     * When passing any number of the functions to pipe in any order
     * Then the resulting string will have been altered by the functions in the order provided
     *
     * @test
     */
    public function pipeCanAlterInputWithProvidedFunctions()
    {
        // create new test functions to pass to pipe
        $appendOne = function (string $str): string {
            $appendStr = 'one';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $appendTwo = function (string $str): string {
            $appendStr = 'two';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $appendThree = function (string $str): string {
            $appendStr = 'three';
            return !$str ? $appendStr : "{$str}-{$appendStr}";
        };
        $strParam = '';

        $threeCallable = Pure::pipe($appendOne, $appendTwo, $appendThree);
        $alteredString = $threeCallable($strParam);
        $this->assertEquals('one-two-three', $alteredString);

        $this->assertEquals('one-two-three-one-two-three', $threeCallable($alteredString));
    }

    /**
     * Given a function that takes three parameters
     * When currying the function and passing less than three parameters
     * Then a function will be returned expecting the remaining parameters
     *
     * @test
     */
    public function curryReturnsAFunctionFromMissingParameters()
    {
        // create a new test function
        $curryTest = function (string $one, string $two, string $three): string {
            return "$one-$two-$three";
        };

        $curryWithTwoParameters = Pure::curry($curryTest);
        $curryWithTwoParameters = $curryWithTwoParameters('one', 'two');
        $this->assertIsCallable($curryWithTwoParameters);

        $curryWithNoParameters = Pure::curry($curryTest);
        $this->assertIsCallable($curryWithNoParameters);

        $curryWithOneParameter = $curryWithNoParameters('one');
        $this->assertIsCallable($curryWithOneParameter);

        $newCurryWithTwoParameter = $curryWithOneParameter('two');
        $this->assertIsCallable($newCurryWithTwoParameter);
    }

    /**
     * Given a function that takes three parameters
     * When currying the function and passing all three parameters at once, or with consecutive calls
     * Then the final return value of the original function will be returned
     *
     * @test
     */
    public function curryReturnsFunctionResultWithAllParameters()
    {
        // create a new test function
        $curryTest = function (string $one, string $two, string $three): string {
            return "$one-$two-$three";
        };

        // curry should take all arguments
        $this->assertEquals('one-two-three', $curryTest('one', 'two', 'three'));

        $newCurry1 = Pure::curry($curryTest)('one');
        $this->assertIsCallable($newCurry1);
        $newCurry2 = $newCurry1('two');
        $this->assertIsCallable($newCurry2);
        $this->assertEquals('one-two-three', $newCurry2('three'));
    }
}
