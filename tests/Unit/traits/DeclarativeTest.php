<?php

namespace Core\Tests\Unit\Traits;

use Core\Tests\TestCase;
use Core\Utilities\Traits\Declarative;

/**
 * Class DeclarativeTest
 *
 * @package Core\Tests\Unit\Traits
 *
 * @group Unit
 * @group Traits
 * @group Declarative
 */
class DeclarativeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function toStringMethodReturnsDescriptionOfClass()
    {
        $implementor = $this->buildDeclarativeClass();
        $classDescription = $implementor->getClassDescription();
        echo $implementor;
        $this->assertStringContainsString('class@anonymous', $classDescription);
        $this->assertStringContainsString('final public getClassDescription(): string', $classDescription);
        $this->assertStringContainsString(
            'private buildMethodDeclaration(array methods, string methodName): array',
            $classDescription
        );
        $this->assertStringContainsString(
            'private buildParameterDeclaration(string paramString, ReflectionParameter param): string',
            $classDescription
        );
        $this->assertStringContainsString(
            'private generateDescriptorLineBuilder(array descriptors, string descriptorIndent, string descriptorPrefix = ""): callable',
            $classDescription
        );
        $this->assertStringContainsString(
            'private generateDescriptorLinesBuilder(array descriptorTypes, string classIndent): callable',
            $classDescription
        );
        $this->assertStringContainsString(
            'private generateDescriptorLinesForClassBuilder(array classDescriptors): callable',
            $classDescription
        );
        $this->assertStringContainsString(
            'private generateMemberDeclarationBuilder(array classVars): callable',
            $classDescription
        );
        $this->assertStringContainsString('private getClassMembers(): array', $classDescription);
        $this->assertStringContainsString('private getClassMethods(): array', $classDescription);
        $this->assertStringContainsString('}', $classDescription);
    }

    private function buildDeclarativeClass()
    {
        return new class
        {
            use Declarative;

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

            /**
             * @return string
             */
            public function __toString(): string
            {
                return $this->getClassDescription();
            }
        };
    }
}
