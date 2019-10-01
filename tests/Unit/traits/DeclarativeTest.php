<?php

namespace Core\Tests\Unit\Traits;

use Core\Tests\TestCase;
use Core\Utilities\Traits\Declarative;

/**
 * Class DeclarativeTest
 *
 * @package Core\Tests\Unit\Traits
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
