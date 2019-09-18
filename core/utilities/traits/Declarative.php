<?php

namespace Core\Utilities\Traits;

/**
 * Trait Declarative
 *
 * @package Core\Utilities\Traits
 */
trait Declarative
{
    /**
     * @return string
     * @throws \ReflectionException
     */
    final public function getClassDescription(): string
    {
        $classDescriptors = array_merge_recursive($this->getClassMembers(), $this->getClassMethods());
        $class = get_class($this);
        // set current class as the first element on the list of class descriptors
        $classDescriptors = [$class => $classDescriptors[$class]] + $classDescriptors;
        $reflectClass = new \ReflectionClass($class);
        $abstractFinal = $reflectClass->isAbstract() ? 'abstract ' : ($reflectClass->isFinal() ? 'final ' : '');
        $interface = $reflectClass->isInterface() ? 'interface ' : '';
        return "\n\n\e[1;32m{$abstractFinal}{$interface}{$class}\x20{\e[0m" . array_reduce(
                array_keys($classDescriptors),
                self::generateDescriptorLinesForClassBuilder($classDescriptors),
                ''
            ) . "\n\e[1;32m}\e[0m\n";
    }

    /**
     * @param array $classDescriptors
     *
     * @return callable
     */
    private function generateDescriptorLinesForClassBuilder(array $classDescriptors): callable
    {
        /**
         * @param string $toString
         * @param string $class
         *
         * @return string
         */
        return function (string $toString, string $class) use ($classDescriptors): string {
            $classIndent = "\e[1;";
            if ($class !== get_class($this)) {
                $reflectClass = new \ReflectionClass($class);
                $abstractFinal = $reflectClass->isAbstract() ? 'abstract ' : ($reflectClass->isFinal() ? 'final ' : '');
                $interface = $reflectClass->isInterface() ? 'interface ' : '';
                $toString .= "\n\n\x20\x20\e[0;32m{$abstractFinal}{$interface}{$class}\e[0m";
                $classIndent = "\x20\x20\e[0;";
            }
            $descriptorTypes = $classDescriptors[$class];
            return array_reduce(
                array_keys($descriptorTypes),
                self::generateDescriptorLinesBuilder($descriptorTypes, $classIndent),
                $toString
            );
        };
    }

    /**
     * @param array $descriptorTypes
     * @param string $classIndent
     *
     * @return callable
     */
    private function generateDescriptorLinesBuilder(array $descriptorTypes, string $classIndent): callable
    {
        /**
         * @param string $toString
         * @param string $descriptorType
         *
         * @return string
         */
        return function (string $toString, string $descriptorType) use (
            $descriptorTypes,
            $classIndent
        ): string {
            $toString .= "\n";
            $descriptorIndent = $classIndent . ($descriptorType === 'members' ? "35m" : "36m");
            $descriptors = $descriptorTypes[$descriptorType];
            return array_reduce(
                array_keys($descriptors),
                self::generateDescriptorLineBuilder($descriptors, $descriptorIndent),
                $toString
            );
        };
    }

    /**
     * @param array $descriptors
     * @param string $descriptorIndent
     * @param string $descriptorPrefix
     *
     * @return callable
     */
    private function generateDescriptorLineBuilder(
        array $descriptors,
        string $descriptorIndent,
        string $descriptorPrefix = ''
    ): callable {
        /**
         * @param string $toString
         * @param string $descriptorKey
         *
         * @return string
         */
        return function (string $toString, string $descriptorKey) use (
            $descriptors,
            $descriptorIndent,
            $descriptorPrefix
        ): string {
            $descriptor = $descriptors[$descriptorKey];
            if (is_string($descriptor)) {
                $toString .= "\n\x20\x20{$descriptorIndent}{$descriptorPrefix}{$descriptor}\e[0m";
                return $toString;
            }
            if (!count($descriptor)) {
                return $toString;
            }
            return array_reduce(
                array_keys($descriptor),
                $this->generateDescriptorLineBuilder($descriptor, $descriptorIndent,
                    "{$descriptorKey}\x20{$descriptorPrefix}"),
                $toString
            );
        };
    }

    /**
     * @return array
     */
    private function getClassMembers(): array
    {
        $classVars = array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
        ksort($classVars);
        return array_reduce(array_keys($classVars), $this->generateMemberDeclarationBuilder($classVars), []);
    }

    /**
     * @param array $classVars
     *
     * @return callable
     */
    private function generateMemberDeclarationBuilder(array $classVars): callable
    {
        $memberClass = '';
        /**
         * @param array $members
         * @param string $memberKey
         *
         * @return array
         */
        return function (array $members, string $memberKey) use ($classVars, &$memberClass) {
            $member = new \ReflectionProperty($this, $memberKey);
            if ($member->class !== $memberClass) {
                $memberClass = $member->class;
                if (!array_key_exists($memberClass, $members) || !array_key_exists('members', $members[$memberClass])) {
                    $staticState = array_merge(['static' => []], []);
                    $members[$memberClass]['members'] = array_merge(
                        ['public' => $staticState, 'protected' => $staticState, 'private' => $staticState],
                        $staticState
                    );
                }
            }
            $memberValue = $classVars[$memberKey];
            switch (gettype($memberValue)) {
                case self::PRIMITIVE_ARRAY:
                case self::PRIMITIVE_OBJECT:
                    $memberValue = json_encode($memberValue);
                    break;
                case self::PRIMITIVE_STRING:
                    $memberValue = '"' . $memberValue . '"';
                    break;
            }
            $arrayPointer = &$members[$memberClass]['members'];
            if ($member->isPublic()) {
                $arrayPointer = &$arrayPointer['public'];
            }
            if ($member->isProtected()) {
                $arrayPointer = &$arrayPointer['protected'];
            }
            if ($member->isPrivate()) {
                $arrayPointer = &$arrayPointer['private'];
            }
            if ($member->isStatic()) {
                $arrayPointer = &$arrayPointer['static'];
            }
            $arrayPointer[$memberKey] = "{$memberKey} = {$memberValue}";
            return $members;
        };
    }

    /**
     * @return array
     */
    private function getClassMethods(): array
    {
        $classMethods = get_class_methods(get_class($this));
        sort($classMethods);
        return array_reduce($classMethods, 'self::buildMethodDeclaration', []);
    }

    /**
     * @param array $methods
     * @param string $methodName
     *
     * @return array
     * @throws \ReflectionException
     */
    private function buildMethodDeclaration(array $methods, string $methodName): array
    {
        static $methodClass = '';
        $method = new \ReflectionMethod($this, $methodName);
        if ($method->class !== $methodClass) {
            $methodClass = $method->class;
            if (!array_key_exists($methodClass, $methods) || !array_key_exists('methods', $methods[$methodClass])) {
                $abstractFinal = ['abstract' => [], 'final' => []];
                $staticState = array_merge(['static' => $abstractFinal], $abstractFinal);
                $methods[$methodClass]['methods'] = array_merge(
                    ['public' => $staticState, 'protected' => $staticState, 'private' => $staticState],
                    $staticState
                );
            }
        }
        $method->getReturnType();
        $parameters = array_reduce($method->getParameters(), 'self::buildParameterDeclaration', '');

        $arrayPointer = &$methods[$methodClass]['methods'];
        if ($method->isPublic()) {
            $arrayPointer = &$arrayPointer['public'];
        }
        if ($method->isProtected()) {
            $arrayPointer = &$arrayPointer['protected'];
        }
        if ($method->isPrivate()) {
            $arrayPointer = &$arrayPointer['private'];
        }
        if ($method->isStatic()) {
            $arrayPointer = &$arrayPointer['static'];
        }
        if ($method->isAbstract()) {
            $arrayPointer = &$arrayPointer['abstract'];
        }
        if ($method->isFinal()) {
            $arrayPointer = &$arrayPointer['final'];
        }
        $returnType = $method->hasReturnType() ? ': ' . $method->getReturnType() : '';
        $arrayPointer[$methodName] = "{$methodName}({$parameters}){$returnType}";
        return $methods;
    }

    /**
     * @param string $paramString
     * @param \ReflectionParameter $param
     *
     * @return string
     */
    private function buildParameterDeclaration(string $paramString, \ReflectionParameter $param): string
    {
        $paramDefault = $param->isDefaultValueAvailable() ? ' = ' . json_encode($param->getDefaultValue()) : '';
        $paramType = $param->hasType() ? $param->getType() . ' ' : '';
        $paramDescription = $paramType . $param->getName() . $paramDefault;
        $paramString .= $paramString ? ', ' . $paramDescription : $paramDescription;
        return $paramString;
    }
}