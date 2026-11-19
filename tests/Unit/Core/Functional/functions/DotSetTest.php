<?php

namespace Tests\Unit\Core\Functional\functions;

use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class DotSetTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('dotSet')]
class DotSetTest extends TestCase
{
    /**
     * Given an object
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     */
    #[Test]
    final public function dotSetRetrievesValueFromObject(): void
    {
        $object = (object)[
            'first' => 'one',
            'nestedOne' => (object)[
                'second' => 'two',
            ],
        ];

        dotSet($object, 'first', 'newOne');
        $this->assertEquals('newOne', $object->first);
        dotSet($object, 'nestedOne.second', 'newTwo');
        $this->assertEquals('newTwo', $object->nestedOne->second);
    }

    /**
     * Given a class instance
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     */
    #[Test]
    final public function dotSetRetrievesValueFromClass(): void
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

        dotSet($class, 'first', 'newOne');
        $this->assertEquals('newOne', $class->first);
        dotSet($class, 'nestedOne.second', 'newTwo');
        $this->assertEquals('newTwo', $class->nestedOne->second);
    }

    /**
     * Given an array
     * When fetching a property with dot-notation,
     * Then the property is returned or default.
     */
    #[Test]
    final public function dotSetRetrievesValuesWithArrays(): void
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

        dotSet($array, '0', 'newOne');
        $this->assertEquals('newOne', $array[0]);
        dotSet($array, '1.second', 'newTwo');
        $this->assertEquals('newTwo', $array[1]['second']);
    }

    /**
     * Given an array
     * When fetching a property with dot-notation containing wildcard (*),
     * Then it will find any match within the array or object.
     */
    #[Test]
    #[DataProvider('dotSetWildcardProvider')]
    final public function dotSetRetrievesValuesWithWildcard(string $path, string $getPath, mixed $expected): void
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

        dotSet($array, $path, 'newValue');
        $testPart = $getPath ? dotGet($array, $getPath) : $array;
        $this->assertEquals($expected, $testPart);
    }

    #[ArrayShape([
        'sets all first level values' => "array",
        'sets all second level values' => "array",
        'sets all third level values' => "array",
        'sets values in the middle of the array' => "array"
    ])]
    final public static function dotSetWildcardProvider(): array
    {
        return [
            'sets all first level values' => [
                'path' => '*',
                'getPath' => '',
                'expected' => [
                    'newValue',
                    'newValue',
                ],
            ],
            'sets all second level values' => [
                'path' => '1.*',
                'getPath' => '1',
                'expected' => [
                        'second' => 'newValue',
                        'nestedOne' => 'newValue',
                        'willHaveDefaults' => 'newValue',
                    ],
            ],
            'sets all third level values' => [
                'path' => '1.nestedOne.*',
                'getPath' => '1.nestedOne',
                'expected' => (object)[
                    'third' => 'newValue',
                ],
            ],
            'sets values in the middle of the array' => [
                'path' => '1.willHaveDefaults.*.hasValue',
                'getPath' => '1.willHaveDefaults',
                'expected' => [
                    'skipped',
                    ['hasValue' => 'newValue'],
                    ['hasValue' => 'newValue'],
                ],
            ],
        ];
    }

    /**
     * Given an array
     * When setting through index 0 (a falsy segment in PHP)
     * Then the value lands in the list, not under a literal '0.name' key.
     */
    #[Test]
    final public function dotSetFollowsIndexZero(): void
    {
        $data = ['list' => [['name' => 'first']]];
        dotSet($data, 'list.0.name', 'changed');
        $this->assertEquals(['list' => [['name' => 'changed']]], $data);
        $empty = [];
        dotSet($empty, '0.name', 'new');
        $this->assertEquals([['name' => 'new']], $empty);
    }
}
