<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class DotNotateTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group dotNotate
 */
class DotNotateTest extends TestCase
{
    /**
     * Given an object with nested properties
     * When calling dotNotate on it
     * Then it should return an associative array with all the dot-notation paths as keys, and the values as values
     *
     * @test
     */
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
}