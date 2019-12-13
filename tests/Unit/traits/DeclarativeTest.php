<?php

namespace Core\Tests\Unit\Traits;

use Core\Tests\Mocks\DataTypeMock;
use Core\Tests\TestCase;

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
     * Given a class having several differently scoped members and inheriting from DataTypeMock
     * When has the Declarative trait and calling getClassDescription on the class
     * Then all class member a method declarations will be output, including relative inheritance
     *
     * @test
     *
     * @throws \ReflectionException
     */
    public function toStringMethodReturnsDescriptionOfClass()
    {
        $implementor = $this->buildDeclarativeClass(0);
        $classDescription = $implementor->getClassDescription(true);

        /*
         * class@anonymous/Users/joshuaheagle/Sites/DesignerCMS/tests/Unit/traits/DeclarativeTest.php0x10b4f18b4 {
         *
         *   static public string staticPublicMember = "static public"
         *   public array memberWithArray = ["firstElement","secondElement"]
         *   public array memberWithArrayAppend = ["firstElement","secondElement"]
         *   public string memberWithDefault = "public"
         *   public array memberWithMap = {"firstIndex":"firstElement","secondIndex":"secondElement"}
         *   public ?string publicMember
         *   static protected string staticProtectedMember = "static protected"
         *   protected ?string protectedMember
         *
         *   public __construct(value, array settings = [])
         *   public __toString(): string
         */
        $this->assertStringContainsString('class@anonymous', $classDescription);

        $this->assertStringContainsString(
            'static public string staticPublicMember = "static public"',
            $classDescription
        );
        $this->assertStringContainsString(
            'public array memberWithArray = ["firstElement","secondElement"]',
            $classDescription
        );
        $this->assertStringContainsString(
            'public array memberWithArrayAppend = ["firstElement","secondElement"]',
            $classDescription
        );
        $this->assertStringContainsString('public string memberWithDefault = "public"', $classDescription);
        $this->assertStringContainsString(
            'public array memberWithMap = {"firstIndex":"firstElement","secondIndex":"secondElement"}',
            $classDescription
        );
        $this->assertStringContainsString('public ?string publicMember', $classDescription);
        $this->assertStringContainsString(
            'static protected string staticProtectedMember = "static protected"',
            $classDescription
        );
        $this->assertStringContainsString('protected ?string protectedMember', $classDescription);

        $this->assertStringContainsString('public __construct(value, array settings = [])', $classDescription);
        $this->assertStringContainsString('public __toString(): string', $classDescription);

        /*
         *   abstract Core\DataTypes\DataType
         *
         *     static protected int systemMaxBits = 64
         *     protected string primitiveType = "object"
         *     protected value = 0
         *
         *     final public getClassDescription(bool includePrivate = false): string
         *     public getPrimitiveType(): string
         *     public getSystemMaxBits(): int
         *     public isEqual(datatype): bool
         *     protected applyMemberSettings(array settings = [])
         *     protected getMember(memberKey)
         *     protected setMember(memberKey, value)
         *     private buildParameterDeclaration(string paramString, ReflectionParameter param): string
         *     private generateDescriptorLineBuilder(array descriptors, string descriptorIndent, string descriptorPrefix = ""): callable
         *     private generateDescriptorLinesBuilder(array descriptorTypes, string classIndent): callable
         *     private generateDescriptorLinesForClassBuilder(array classDescriptors): callable
         *     private generateMemberDeclarationBuilder(array classVars, bool includePrivate = false): callable
         *     private generateMethodDeclarationBuilder(bool includePrivate = false): callable
         *     private getClassMembers(bool includePrivate = false): array
         *     private getClassMethods(bool includePrivate = false): array
         */
        $this->assertStringContainsString('abstract Core\DataTypes\DataType', $classDescription);

        $this->assertStringContainsString('static protected int systemMaxBits = 64', $classDescription);
        $this->assertStringContainsString('protected string primitiveType = "object"', $classDescription);
        $this->assertStringContainsString('protected value', $classDescription);

        $this->assertStringContainsString(
            'final public getClassDescription(bool includePrivate = false): string',
            $classDescription
        );
        $this->assertStringContainsString('public getPrimitiveType(): string', $classDescription);
        $this->assertStringContainsString('public getSystemMaxBits(): int', $classDescription);
        $this->assertStringContainsString('public isEqual(dataType): bool', $classDescription);
        $this->assertStringContainsString('protected applyMemberSettings(array settings = [])', $classDescription);
        $this->assertStringContainsString('protected getMember(memberKey)', $classDescription);
        $this->assertStringContainsString('protected setMember(memberKey, value)', $classDescription);
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
            'private generateMemberDeclarationBuilder(array classVars, bool includePrivate = false): callable',
            $classDescription
        );
        $this->assertStringContainsString(
            'private generateMethodDeclarationBuilder(bool includePrivate = false): callable',
            $classDescription
        );
        $this->assertStringContainsString(
            'private getClassMembers(bool includePrivate = false): array',
            $classDescription
        );
        $this->assertStringContainsString(
            'private getClassMethods(bool includePrivate = false): array',
            $classDescription
        );

        /*
         *   Core\Tests\Mocks\DataTypeMock
         *
         *     public getValue()
         *     public setValue(value)
         *
         * }
         */
        $this->assertStringContainsString('Core\Tests\Mocks\DataTypeMock', $classDescription);

        $this->assertStringContainsString('public getValue()', $classDescription);
        $this->assertStringContainsString('public setValue(value)', $classDescription);

        $this->assertStringContainsString('}', $classDescription);
    }

    /**
     * Return an example class having a variety of members demonstrate Declarative trait.
     *
     * @param null $value
     *
     * @return \Core\Tests\Mocks\DataTypeMock
     */
    private function buildDeclarativeClass($value = null): DataTypeMock
    {
        return new class($value) extends DataTypeMock {
            const CONSTANT_MEMBER = 'constant';
            const CONSTANT_ARRAY_MEMBER = [
                self::CONSTANT_MEMBER,
                'anotherElement',
            ];
            const CONSTANT_ASSOC_ARRAY_MEMBER = [
                'firstElement' => self::CONSTANT_MEMBER,
                'secondElement' => 'anotherElement',
            ];
            static public string $staticPublicMember = 'static public';
            public array $memberWithArray = ['firstElement', 'secondElement'];
            public array $memberWithArrayAppend = ['firstElement', 'secondElement'];
            public string $memberWithDefault = 'public';
            public array $memberWithMap = ['firstIndex' => 'firstElement', 'secondIndex' => 'secondElement'];
            public ?string $publicMember;
            static protected string $staticProtectedMember = 'static protected';
            protected ?string $protectedMember;
            static private string $staticPrivateMember = 'static private';
            private ?string $privateMember;

            /**
             * Declarative class constructor.
             *
             * @param $value
             * @param array $settings
             */
            public function __construct($value, array $settings = [])
            {
                parent::__construct($value, $settings);
            }

            /**
             * Have this class show its declarations when being echoed.
             *
             * @return string
             */
            public function __toString(): string
            {
                return $this->getClassDescription();
            }
        };
    }
}
