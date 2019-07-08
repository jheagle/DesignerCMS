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
        $this->tt('some test')($curryWithTwoParameters);
        $this->assertIsCallable($curryWithTwoParameters);

        $curryWithNoParameters = Pure::curry($curryTest);
        $this->assertIsCallable($curryWithNoParameters);

        $curryWithOneParameter = $curryWithNoParameters('one');
        $this->assertIsCallable($curryWithOneParameter);

        $newCurryWithTwoParameter = $curryWithOneParameter('two');
        $this->assertIsCallable($newCurryWithTwoParameter);

        $this->assertEquals('one-two-three', $newCurryWithTwoParameter('three'));
    }

    /**
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
        $newCurry1 = Pure::curry($curryTest)('one');
        $this->assertIsCallable($newCurry1);
        $newCurry2 = $newCurry1('two');
        $this->assertIsCallable($newCurry2);
        $this->assertEquals('one-two-three', $newCurry2('three'));
        $newCurry1 = Pure::curry($curryTest)('one');
        $this->assertIsCallable($newCurry1);
        $newCurry2 = $newCurry1('two');
        $this->assertIsCallable($newCurry2);
        $this->assertEquals('one-two-three', $newCurry2('three'));
    }
}
