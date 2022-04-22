<?php

namespace Tests\Unit\Core\Traits;

use Tests\Mocks\DataTypeMock;
use Tests\TestCase;

/**
 * Class DeclarativeTest
 *
 * @package Tests\Unit\Core\Traits
 *
 * @group Unit
 * @group Traits
 * @group Declarative
 */
class DeclarativeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Given a class having several differently scoped members and inheriting from DataTypeMock
     * When has the Declarative trait and calling getClassDescription on the class
     * Then all class member a method declarations will be output, including relative inheritance
     *
     * @test
     */
    public function toStringMethodReturnsDescriptionOfClass()
    {
        $implementor = $this->buildDeclarativeClass(0);
        $classDescription = $implementor->getClassDescription(true);
        /*
         * Tests\Mocks\DataTypeMock@anonymous {
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
         *   final public testFinal(): void
         *   public __construct(value, array settings = [])
         *   public __toString(): string
         */
        $this->assertStringContainsString('Tests\Mocks\DataTypeMock@anonymous', $classDescription);

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

        $this->assertStringContainsString('final public testFinal(): void', $classDescription);
        $this->assertStringContainsString('public __construct(value, array settings = [])', $classDescription);
        $this->assertStringContainsString('public __toString(): string', $classDescription);

        /*
         *   abstract Core\DataTypes\DataType
         *
         *     static protected int systemMaxBits
         *     protected string primitiveType = "object"
         *     protected ?mixed value = 0
         *
         *     public applyMemberSettings(array settings = []): self
         *     public getAllMembers(bool useDefaults = false): array
         *     public getClassDescription(bool includePrivate = false): string
         *     public getMember(string memberKey): ?mixed
         *     public getPrimitiveType(): string
         *     public getSystemMaxBits(): int
         *     public isEqual(?mixed datatype): bool
         *     public setMember(string memberKey, ?mixed value): ?mixed
         *     private buildParameterDeclaration(string paramString, ReflectionParameter param): string
         *     private generateDescriptorLineBuilder(array descriptors, string descriptorIndent, string descriptorPrefix = ""): callable
         *     private generateDescriptorLinesBuilder(array descriptorTypes, string classIndent): callable
         *     private generateDescriptorLinesForClassBuilder(array classDescriptors): callable
         *     private generateMemberDeclarationBuilder(array classVars, bool includePrivate = false): callable
         *     private generateMethodDeclarationBuilder(bool includePrivate = false): callable
         *     private getClassMembers(bool includePrivate = false): array
         *     private getClassMethods(bool includePrivate = false): array
         *     private isStaticMember(string memberKey): bool
         */
        $this->assertStringContainsString('abstract Core\DataTypes\DataType', $classDescription);

        $this->assertStringContainsString('static protected int systemMaxBits', $classDescription);
        $this->assertStringContainsString('protected string primitiveType = "object"', $classDescription);
        $this->assertStringContainsString('protected ?mixed value', $classDescription);

        $this->assertStringContainsString('public applyMemberSettings(array settings = []): self', $classDescription);
        $this->assertStringContainsString('public getAllMembers(bool useDefaults = false): array', $classDescription);
        $this->assertStringContainsString(
            'public getClassDescription(bool includePrivate = false): string',
            $classDescription
        );
        $this->assertStringContainsString('public getMember(string memberKey): ?mixed', $classDescription);
        $this->assertStringContainsString('public getPrimitiveType(): string', $classDescription);
        $this->assertStringContainsString('public getSystemMaxBits(): int', $classDescription);
        $this->assertStringContainsString('public isEqual(?mixed dataType): bool', $classDescription);
        $this->assertStringContainsString(
            'public setMember(string memberKey, ?mixed value): ?mixed',
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
         *   Tests\Mocks\DataTypeMock
         *
         *     public getValue(): ?mixed
         *     public setValue(?mixed value): ?mixed
         *
         * }
         */
        $this->assertStringContainsString('Tests\Mocks\DataTypeMock', $classDescription);

        $this->assertStringContainsString('public getValue(): ?mixed', $classDescription);
        $this->assertStringContainsString('public setValue(?mixed value): ?mixed', $classDescription);

        $this->assertStringContainsString('}', $classDescription);
    }

    /**
     * Return an example class having a variety of members demonstrate Declarative trait.
     *
     * @param null $value
     *
     * @return DataTypeMock
     */
    private function buildDeclarativeClass($value = null): DataTypeMock
    {
        return new class($value) extends DataTypeMock {
            public const CONSTANT_MEMBER = 'constant';
            public const CONSTANT_ARRAY_MEMBER = [
                self::CONSTANT_MEMBER,
                'anotherElement',
            ];
            public const CONSTANT_ASSOC_ARRAY_MEMBER = [
                'firstElement' => self::CONSTANT_MEMBER,
                'secondElement' => 'anotherElement',
            ];
            public static string $staticPublicMember = 'static public';
            public array $memberWithArray = ['firstElement', 'secondElement'];
            public array $memberWithArrayAppend = ['firstElement', 'secondElement'];
            public string $memberWithDefault = 'public';
            public array $memberWithMap = ['firstIndex' => 'firstElement', 'secondIndex' => 'secondElement'];
            public ?string $publicMember;
            protected static string $staticProtectedMember = 'static protected';
            protected ?string $protectedMember;
            private static string $staticPrivateMember = 'static private';
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

            final public function testFinal(): void
            {
                //
            }
        };
    }
}
