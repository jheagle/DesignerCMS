<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class DotGetTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group dotGet
 */
class DotGetTest extends TestCase
{
    /**
     * Given an object
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     *
     * @test
     */
    final public function dotGetRetrievesValueFromObject(): void
    {
        $object = (object)[
            'first' => 'one',
            'nestedOne' => (object)[
                'second' => 'two',
            ],
        ];

        $this->assertEquals('one', dotGet($object, 'first'));
        $this->assertEquals('two', dotGet($object, 'nestedOne.second'));
        $this->assertNull(dotGet($object, 'nestedFake.third'));
    }

    /**
     * Given a class instance
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     *
     * @test
     */
    final public function dotGetRetrievesValueFromClass(): void
    {
        $nestedObject = (object)[
            'second' => 'two',
        ];
        $class = new class ($nestedObject) {
            public string $first = 'one';
            public ?object $nestedOne = null;

            public function __construct(object $nestedObject)
            {
                $this->nestedOne = $nestedObject;
            }
        };

        $this->assertEquals('one', dotGet($class, 'first'));
        $this->assertEquals('two', dotGet($class, 'nestedOne.second'));
        $this->assertNull(dotGet($class, 'nestedFake.third'));
    }

    /**
     * Given an array
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     *
     * @test
     */
    final public function dotGetRetrievesValuesWithArrays(): void
    {
        $array = [
            'one',
            [
                'second' => 'two',
                'nestedOne' => (object)[
                    'third' => 'three',
                ],
            ],
        ];

        $this->assertEquals('one', dotGet($array, '0'));
        $this->assertEquals('two', dotGet($array, '1.second'));
        $this->assertEquals('three', dotGet($array, '1.nestedOne.third'));
        $this->assertNull(dotGet($array, 'nestedFake.third'));
    }

    /**
     * Given an array
     * When fetching a property with dot-notation containing wildcard (*),
     * Then it will find any match within the array or object.
     *
     * @test
     */
    final public function dotGetRetrievesValuesWithWildcard(): void
    {
        $array = [
            'one',
            [
                'second' => 'two',
                'nestedOne' => (object)[
                    'third' => 'three',
                ],
                'willHaveDefaults' => [
                    'skipped',
                    ['hasValue' => 'First Value'],
                    ['hasValue' => 'Second Value'],
                ]
            ],
        ];

        $this->assertEquals($array, dotGet($array, '*'));
        $this->assertEquals($array[1], dotGet($array, '1.*'));
        $this->assertEquals($array[1]['nestedOne'], dotGet($array, '1.nestedOne.*'));
        $this->assertEquals(
            [
                1 => 'First Value',
                2 => 'Second Value',
            ],
            dotGet($array, '1.willHaveDefaults.*.hasValue')
        );
    }
}