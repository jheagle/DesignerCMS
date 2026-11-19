<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class DotNotateTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('dotNotate')]
class DotNotateTest extends TestCase
{
    /**
     * Given an object with nested properties
     * When calling dotNotate on it
     * Then it should return an associative array with all the dot-notation paths as keys, and the values as values
     */
    #[Test]
    final public function dotNotateBuildsSingleDimensionArrayFromNestedObjects(): void
    {
        $object = (object)[
            'first' => 'one',
            'nestedOne' => [
                'second' => 'two',
                'deeperNested' => (object)[
                    'third' => 'three',
                ],
            ],
            'nestedArray' => [
                'arrayOne',
                'arrayTwo',
            ],
        ];

        $this->assertEquals(
            [
                'first' => 'one',
                'nestedOne.second' => 'two',
                'nestedOne.deeperNested.third' => 'three',
                'nestedArray.0' => 'arrayOne',
                'nestedArray.1' => 'arrayTwo',
            ],
            dotNotate($object)
        );
    }

    /**
     * Given empty arrays and objects, at the top and nested
     * When calling dotNotate
     * Then each stays as a value of its own (they used to disappear).
     */
    #[Test]
    final public function dotNotateKeepsEmptyContainers(): void
    {
        $data = [
            'emptyArray' => [],
            'emptyObject' => (object)[],
            'nested' => ['inner' => [], 'value' => 1],
        ];
        $this->assertEquals(
            [
                'emptyArray' => [],
                'emptyObject' => (object)[],
                'nested.inner' => [],
                'nested.value' => 1,
            ],
            dotNotate($data)
        );
    }

    /**
     * Given nested data with lists and empty containers
     * When it is dot-notated and then rebuilt with dotSet
     * Then the rebuilt data equals the original.
     */
    #[Test]
    final public function dotNotateRoundTripsThroughDotSet(): void
    {
        $data = ['list' => [['name' => 'first'], []], 'emptyArray' => [], 'nested' => ['inner' => [], 'value' => 1]];
        $rebuilt = [];
        foreach (dotNotate($data) as $path => $value) {
            dotSet($rebuilt, $path, $value);
        }
        $this->assertEquals($data, $rebuilt);
    }
}
