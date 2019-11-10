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
     * Given two integers
     * When adding these numbers
     * Then the result is the sum of the two numbers
     *
     * @test
     */
    public function addCanMakeATotal()
    {
        $this->assertEquals(2, Pure::add(1, 1));
        $this->assertEquals(0, Pure::add(1, -1));
        $this->assertEquals(-2, Pure::add(-1, -1));
        $this->assertEquals(3, Pure::add(1, 2));
        $this->assertEquals(-1, Pure::add(1, -2));
        $this->assertEquals(1234567940, Pure::add(50, 1234567890));
        // Test overflow
        $this->assertEquals(PHP_INT_MIN, Pure::add(1, PHP_INT_MAX));
        $this->assertEquals(PHP_INT_MAX, Pure::add(-1, PHP_INT_MIN));
        // This add function works on int not floats
        $this->assertEquals(2, Pure::add(1.1, 1.1));
    }

    /**
     * Given a default or function to compare with
     * When using the coalesce function
     * Then if no values are provided it will be null or the default, otherwise the first value to not match.
     *
     * @test
     */
    public function coalesceReturnsFirstNotMatchingOrDefault()
    {
        $this->assertNull(Pure::coalesce());
        $this->assertNull(Pure::coalesce(function ($value) {
            return $value !== null;
        }));
        $this->assertEquals(3, Pure::coalesce(3));
        $this->assertNull(Pure::coalesce(function ($value) {
            return $value !== 3;
        }));
        $this->assertEquals('something', Pure::coalesce(function ($value) {
            return !!$value;
        }, 0, false, null, '', '0', [], 0.0, 'something'));
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

    /**
     * Given a default and a value
     * When using the defaultValue function on truthy or falsy values
     * Then if the value is truthy then return the value, otherwise return the default.
     *
     * @test
     */
    public function defaultValueReturnsTheValueOrDefaultIfFalse()
    {
        $this->assertTrue(Pure::defaultValue('true is truthy', true));
        $this->assertEquals('false is falsy', Pure::defaultValue('false is falsy', false));
        $this->assertEquals('null is falsy', Pure::defaultValue('null is falsy', null));
        $this->assertEquals('zero is falsy', Pure::defaultValue('zero is falsy', 0));
        $this->assertEquals('float zero is falsy', Pure::defaultValue('float zero is falsy', 0.0));
        $this->assertEquals('string zero is falsy', Pure::defaultValue('string zero is falsy', '0'));
        $this->assertEquals('empty string is falsy', Pure::defaultValue('empty string is falsy', ''));
        $this->assertEquals('empty array is falsy', Pure::defaultValue('empty array is falsy', []));
    }

    /**
     * Given a max value
     * When applying a number to maxBound
     * Then the result can be the original number but not higher than max value
     *
     * @test
     */
    public function maxBoundCanLimitANumber()
    {
        $this->assertEquals(1, Pure::maxBound(1, 1));
        $this->assertEquals(-1, Pure::maxBound(1, -1));
        $this->assertEquals(-1, Pure::maxBound(-1, -1));
        $this->assertEquals(1, Pure::maxBound(1, 2));
        $this->assertEquals(-2, Pure::maxBound(1, -2));
        $this->assertEquals(50, Pure::maxBound(50, 1234567890));
        $this->assertEquals(1, Pure::maxBound(1, PHP_INT_MAX));
        $this->assertEquals(PHP_INT_MIN, Pure::maxBound(-1, PHP_INT_MIN));
        // Test floats
        $this->assertEquals(1.1, Pure::maxBound(1.1, 1.1));
    }

    /**
     * Given a min value
     * When applying a number to minBound
     * Then the result can be the original number but not lower than min value
     *
     * @test
     */
    public function minBoundCanLimitANumber()
    {
        $this->assertEquals(1, Pure::minBound(1, 1));
        $this->assertEquals(1, Pure::minBound(1, -1));
        $this->assertEquals(-1, Pure::minBound(-1, -1));
        $this->assertEquals(2, Pure::minBound(1, 2));
        $this->assertEquals(1, Pure::minBound(1, -2));
        $this->assertEquals(1234567890, Pure::minBound(50, 1234567890));
        $this->assertEquals(PHP_INT_MAX, Pure::minBound(1, PHP_INT_MAX));
        $this->assertEquals(-1, Pure::minBound(-1, PHP_INT_MIN));
        // Test floats
        $this->assertEquals(1.1, Pure::minBound(1.1, 1.1));
    }

    /**
     * Given an integer
     * When applying the integer to negate
     * Then the result will be the same absolute value but with the signed bit switched
     *
     * @test
     */
    public function negateWillToggleTheSignedBit()
    {
        $this->assertEquals(-1, Pure::negate(1));
        $this->assertEquals(1, Pure::negate(-1));
        $this->assertEquals(-2, Pure::negate(2));
        $this->assertEquals(2, Pure::negate(-2));
        $this->assertEquals(PHP_INT_MIN + 1, Pure::negate(PHP_INT_MAX));
        // Absolute value of PHP_INT_MIN is greater than PHP_INT_MAX (by 1), so negating wraps back to negative
        $this->assertEquals(PHP_INT_MIN, Pure::negate(PHP_INT_MIN));
        // This negate function works on int not floats (leverages Pure::add)
        $this->assertEquals(-1, Pure::negate(1.1));
    }

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
     * Given any function or method (with class)
     * When passing the function or method to requiredParameterCount
     * Then the number of arguments which are needed to use the function will be returned.
     *
     * @test
     */
    public function countRequiredParametersReturnsCorrectNumberOfParameters()
    {
        $this->assertEquals(2, Pure::requiredParameterCount([Pure::class, 'add']));
        $this->assertEquals(0, Pure::requiredParameterCount([Pure::class, 'coalesce']));
        $customFunction = function (int $one, int $two, int $three, int $four = 0): int {
            return $one + $two + $three + $four;
        };
        $this->assertEquals(3, Pure::requiredParameterCount($customFunction));
        $customClass = new class
        {
            function customMethod($arg1, $arg2, ...$moreArgs)
            {
                return 'something';
            }
        };
        $this->assertEquals(2, Pure::requiredParameterCount([get_class($customClass), 'customMethod']));
    }
}
