<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class DotGetTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('dotGet')]
class DotGetTest extends TestCase
{
    /**
     * Given an object
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
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

    /**
     * Given a list inside an array or object
     * When reading through index 0 (a falsy segment in PHP)
     * Then the path continues past it ('list.0.name' used to stop at '0' and look up '0.name').
     */
    #[Test]
    final public function dotGetFollowsIndexZero(): void
    {
        $data = ['list' => [['name' => 'first'], ['name' => 'second']]];
        $this->assertEquals('first', dotGet($data, 'list.0.name'));
        $this->assertEquals('second', dotGet($data, 'list.1.name'));
        $this->assertEquals('first', dotGet((object)$data, 'list.0.name'));
        $this->assertEquals('first', dotGet([['name' => 'first']], '0.name'));
        $this->assertEquals('default', dotGet($data, 'list.0.missing', 'default'));
    }
}
