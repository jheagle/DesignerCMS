<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class PipeTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group pipe
 */
class PipeTest extends TestCase
{
    /**
     * Given four string altering functions and a blank string variable
     * When passing any number of the functions to pipe in any order
     * Then pipe returns a callable function which will pass a parameter to all of the provided functions
     *
     * @test
     */
    final public function pipeCanReceiveMultipleFunctions(): void
    {
        // create new test functions to pass to pipe
        $appendOne = function (string $str): string {
            $appendStr = 'one';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $appendTwo = function (string $str): string {
            $appendStr = 'two';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $appendThree = function (string $str): string {
            $appendStr = 'three';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $strParam = '';

        $oneCallable = pipe($appendOne);
        $this->assertIsCallable($oneCallable);
        $this->assertEquals('one', $oneCallable($strParam));
        $twoCallable = pipe($appendOne, $appendTwo);
        $this->assertIsCallable($twoCallable);
        $this->assertEquals('one-two', $twoCallable($strParam));
        $threeCallable = pipe($appendOne, $appendTwo, $appendThree);
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
    final public function pipeCanAlterInputWithProvidedFunctions(): void
    {
        // create new test functions to pass to pipe
        $appendOne = function (string $str): string {
            $appendStr = 'one';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $appendTwo = function (string $str): string {
            $appendStr = 'two';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $appendThree = function (string $str): string {
            $appendStr = 'three';
            return !$str ? $appendStr : "$str-$appendStr";
        };
        $strParam = '';

        $threeCallable = pipe($appendOne, $appendTwo, $appendThree);
        $alteredString = $threeCallable($strParam);
        $this->assertEquals('one-two-three', $alteredString);

        $this->assertEquals('one-two-three-one-two-three', $threeCallable($alteredString));
    }
}