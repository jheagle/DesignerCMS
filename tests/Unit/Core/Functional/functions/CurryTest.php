<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class CurryTest
 * @package Tests\Unit\Core\Functional\functions
 * 
 * @group Unit
 * @group Functional
 * @group Pure
 * @group curry
 */
class CurryTest extends TestCase
{
    /**
     * Given a function that takes three parameters
     * When currying the function and passing less than three parameters
     * Then a function will be returned expecting the remaining parameters
     *
     * @test
     */
    final public function curryReturnsAFunctionFromMissingParameters(): void
    {
        // create a new test function
        $curryTest = function (string $one, string $two, string $three): string {
            return "$one-$two-$three";
        };

        $curryWithTwoParameters = curry($curryTest);
        $curryWithTwoParameters = $curryWithTwoParameters('one', 'two');
        $this->assertIsCallable($curryWithTwoParameters);

        $curryWithNoParameters = curry($curryTest);
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
    final public function curryReturnsFunctionResultWithAllParameters(): void
    {
        // create a new test function
        $curryTest = function (string $one, string $two, string $three): string {
            return "$one-$two-$three";
        };

        // curry should take all arguments
        $this->assertEquals('one-two-three', $curryTest('one', 'two', 'three'));

        $newCurry1 = curry($curryTest)('one');
        $this->assertIsCallable($newCurry1);
        $newCurry2 = $newCurry1('two');
        $this->assertIsCallable($newCurry2);
        $this->assertEquals('one-two-three', $newCurry2('three'));
    }
}