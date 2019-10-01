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
            'publicMemberWithDefault' => 'updated public',
            'protectedMember' => 'assigned protected member',
            'protectedMemberWithDefault' => 'updated protected',
            'privateMember' => 'assigned private member',
            'privateMemberWithDefault' => 'updated private',
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
        $this->assertEquals('updated public', $getMember('publicMemberWithDefault'));
        $this->assertEquals('assigned protected member', $getMember('protectedMember'));
        $this->assertEquals('updated protected', $getMember('protectedMemberWithDefault'));
        $this->assertEquals('assigned private member', $getMember('privateMember'));
        $this->assertEquals('updated private', $getMember('privateMemberWithDefault'));
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
            // TODO: Add in Final to see if it throws errors / exceptions
            // TODO: Add in different types, not only strings, especially consider arrays, objects, and Resources
            static public $staticPublicMember = 'static public';
            static protected $staticProtectedMember = 'static protected';
            static private $staticPrivateMember = 'static private';
            public $publicMember;
            public $publicMemberWithDefault = 'public';
            protected $protectedMember;
            protected $protectedMemberWithDefault = 'protected';
            private $privateMember;
            private $privateMemberWithDefault = 'private';
        };
    }
}
