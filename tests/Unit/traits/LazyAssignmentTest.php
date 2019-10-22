<?php

namespace Core\Tests\Unit\Traits;

use Core\DataTypes\Potential;
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
     * Given a class having several differently scoped members
     * When calling applyMemberSettings with a keyed array for all existing members
     * Then all members which can be updated will be updated, only constants cannot be updated
     *
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
            'PROTECTED_CONSTANT' => 'changedConstant',
            'PROTECTED_CONSTANT_ARRAY' => [
                'anotherChangedConstant',
                'updatedAnotherElement',
            ],
            'PROTECTED_CONSTANT_ASSOC_ARRAY' => [
                'firstElement' => 'anotherChangedConstant',
                'secondElement' => 'updatedAnotherElement',
                'thirdElement' => 'finalUpdatedElement',
            ],
            'PRIVATE_CONSTANT' => 'changedConstant',
            'PRIVATE_CONSTANT_ARRAY' => [
                'anotherChangedConstant',
                'updatedAnotherElement',
            ],
            'PRIVATE_CONSTANT_ASSOC_ARRAY' => [
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
        $this->assertEquals('protected constant', $getMember('PROTECTED_CONSTANT'));
        $this->assertEquals(['protected constant', 'protectedElement'], $getMember('PROTECTED_CONSTANT_ARRAY'));
        $this->assertEquals([
            'firstElement' => 'protected constant',
            'secondElement' => 'protectedElement',
        ], $getMember('PROTECTED_CONSTANT_ASSOC_ARRAY'));
        $this->assertEquals('private constant', $getMember('PRIVATE_CONSTANT'));
        $this->assertEquals(['private constant', 'privateElement'], $getMember('PRIVATE_CONSTANT_ARRAY'));
        $this->assertEquals([
            'firstElement' => 'private constant',
            'secondElement' => 'privateElement',
        ], $getMember('PRIVATE_CONSTANT_ASSOC_ARRAY'));
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

    /**
     * Create a class having a sample of varying scoped members and using LazyAssignment.
     *
     * @return Potential
     */
    private function buildLazyAssignmentClass(): Potential
    {
        return new class implements Potential
        {
            use LazyAssignment;

            public const CONSTANT_MEMBER = 'constant';
            public const CONSTANT_ARRAY_MEMBER = [
                self::CONSTANT_MEMBER,
                'anotherElement',
            ];
            public const CONSTANT_ASSOC_ARRAY_MEMBER = [
                'firstElement' => self::CONSTANT_MEMBER,
                'secondElement' => 'anotherElement',
            ];
            protected const PROTECTED_CONSTANT = 'protected constant';
            protected const PROTECTED_CONSTANT_ARRAY = [
                self::PROTECTED_CONSTANT,
                'protectedElement',
            ];
            protected const PROTECTED_CONSTANT_ASSOC_ARRAY = [
                'firstElement' => self::PROTECTED_CONSTANT,
                'secondElement' => 'protectedElement',
            ];
            private const PRIVATE_CONSTANT = 'private constant';
            private const PRIVATE_CONSTANT_ARRAY = [
                self::PRIVATE_CONSTANT,
                'privateElement',
            ];
            private const PRIVATE_CONSTANT_ASSOC_ARRAY = [
                'firstElement' => self::PRIVATE_CONSTANT,
                'secondElement' => 'privateElement',
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

            public function __toString(): string
            {
                return get_class($this);
            }
        };
    }
}
