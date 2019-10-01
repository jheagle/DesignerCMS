<?php

namespace Core\Tests\Unit\Traits;

use Core\Tests\TestCase;
use Core\Tests\Traits\IgnoreMethodScopes;
use Core\Utilities\Traits\LazyAssignment;

/**
 * Class LazyAssignmentTest
 *
 * @package Core\Tests\Unit\Traits
 *
 * @group Unit
 * @group Traits
 * @group LazyAssignment
 */
class LazyAssignmentTest extends TestCase
{
    use IgnoreMethodScopes;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function allMembersTypesAssigned()
    {
        $implementor = $this->buildLazyAssignmentClass();
        $applyMemberSettings = $this->accessNonPublicMethod($implementor, 'applyMemberSettings');
        $updatedImplementor = $applyMemberSettings([
            'CONSTANT_MEMBER' => 'changedConstant',
            'CONSTANT_ARRAY_MEMBER' => [
                'anotherChangedConstant',
                'updatedAnotherElement',
            ],
            'CONSTANT_ASSOC_ARRAY_MEMBER' => [
                'firstElement' => 'anotherChangedConstant',
                'secondElement' => 'updatedAnotherElement',
                'thirdElement' => 'finalUpdatedElement',
            ],
            'staticPublicMember' => 'updated static public',
            'staticProtectedMember' => 'updated static protected',
            'staticPrivateMember' => 'updated static private',
            'publicMember' => 'assigned public member',
            'protectedMember' => 'assigned protected member',
            'privateMember' => 'assigned private member',
            'memberWithDefault' => 'updated public',
            'memberWithArray' => ['thirdElement', 'fourthElement'],
            'memberWithArrayAppend' => [2 => 'thirdElement', 3 => 'fourthElement'],
            'memberWithMap' => [
                'secondIndex' => 'updatedSecondElement',
                'thirdIndex' => 'thirdElement',
                'fourthIndex' => 'fourthElement',
            ],
        ]);
        $getMember = $this->accessNonPublicMethod($updatedImplementor, 'getMember');
        // Constants cannot be changed
        $this->assertEquals('constant', $getMember('CONSTANT_MEMBER'));
        $this->assertEquals(['constant', 'anotherElement'], $getMember('CONSTANT_ARRAY_MEMBER'));
        $this->assertEquals([
            'firstElement' => 'constant',
            'secondElement' => 'anotherElement',
        ], $getMember('CONSTANT_ASSOC_ARRAY_MEMBER'));
        $this->assertEquals('updated static public', $getMember('staticPublicMember'));
        $this->assertEquals('updated static protected', $getMember('staticProtectedMember'));
        $this->assertEquals('updated static private', $getMember('staticPrivateMember'));
        $this->assertEquals('assigned public member', $getMember('publicMember'));
        $this->assertEquals('assigned protected member', $getMember('protectedMember'));
        $this->assertEquals('assigned private member', $getMember('privateMember'));
        $this->assertEquals('updated public', $getMember('memberWithDefault'));
        $this->assertEquals(['thirdElement', 'fourthElement'], $getMember('memberWithArray'));
        $this->assertEquals([
            'firstElement',
            'secondElement',
            'thirdElement',
            'fourthElement',
        ], $getMember('memberWithArrayAppend'));
        $this->assertEquals([
            'firstIndex' => 'firstElement',
            'secondIndex' => 'updatedSecondElement',
            'thirdIndex' => 'thirdElement',
            'fourthIndex' => 'fourthElement',
        ], $getMember('memberWithMap'));
    }

    private function buildLazyAssignmentClass()
    {
        return new class
        {
            use LazyAssignment;

            const CONSTANT_MEMBER = 'constant';
            const CONSTANT_ARRAY_MEMBER = [
                self::CONSTANT_MEMBER,
                'anotherElement',
            ];
            const CONSTANT_ASSOC_ARRAY_MEMBER = [
                'firstElement' => self::CONSTANT_MEMBER,
                'secondElement' => 'anotherElement',
            ];
            static public $staticPublicMember = 'static public';
            static protected $staticProtectedMember = 'static protected';
            static private $staticPrivateMember = 'static private';
            public $publicMember;
            protected $protectedMember;
            private $privateMember;
            public $memberWithDefault = 'public';
            public $memberWithArray = ['firstElement', 'secondElement'];
            public $memberWithArrayAppend = ['firstElement', 'secondElement'];
            public $memberWithMap = ['firstIndex' => 'firstElement', 'secondIndex' => 'secondElement'];
        };
    }
}
