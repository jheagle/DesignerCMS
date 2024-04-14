<?php

namespace Tests\Unit\Core\Functional\functions;

use Core\Utilities\Functional\Pure;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class RequiredParameterCountTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('requiredParameterCount')]
class RequiredParameterCountTest extends TestCase
{
    /**
     * Given any function or method (with class)
     * When passing the function or method to requiredParameterCount
     * Then the number of arguments which are needed to use the function will be returned.
     */
    #[Test]
    final public function countRequiredParametersReturnsCorrectNumberOfParameters(): void
    {
        $this->assertEquals(2, requiredParameterCount([Pure::class, 'add']));
        $this->assertEquals(0, requiredParameterCount([Pure::class, 'coalesce']));
        $customFunction = function (int $one, int $two, int $three, int $four = 0): int {
            return $one + $two + $three + $four;
        };
        $this->assertEquals(3, requiredParameterCount($customFunction));
        $customClass = new class {
            /**
             * Some overly done test method that takes some strings and returns a concatenation of the strings.
             *
             * @param string $arg1
             * @param string $arg2
             * @param string[] ...$moreArgs
             *
             * @return string
             */
            final public function customMethod(string $arg1, string $arg2, ...$moreArgs): string
            {
                return array_reduce($moreArgs, function (string $result, string $anArg): string {
                    $result .= " $anArg";
                    return $result;
                }, "$arg1 $arg2");
            }
        };
        $this->assertEquals(2, requiredParameterCount([get_class($customClass), 'customMethod']));
    }
}