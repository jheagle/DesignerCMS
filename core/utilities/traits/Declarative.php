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
     * Get a string representation of all of this classes member and method declarations.
     *
     * @return string
     *
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
            ) . "\n\n\e[1;32m}\e[0m\n";
    }

    /**
     * Store all of the descriptors, then return the buildDescriptorLinesForClass function.
     *
     * @param array $classDescriptors
     *
     * @return callable
     */
    private function generateDescriptorLinesForClassBuilder(array $classDescriptors): callable
    {
        /**
         * Given a string to append to, a class name (could be this class or inherited class), build the associated
         * descriptor lines.
         *
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
     * Store all of the descriptor groups and class indent states, then return the buildDescriptorLines function.
     *
     * @param array $descriptorTypes
     * @param string $classIndent
     *
     * @return callable
     */
    private function generateDescriptorLinesBuilder(array $descriptorTypes, string $classIndent): callable
    {
        /**
         * Given a descriptor group, add in any styles to distinguish this group.
         *
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
     * Store a group of descriptors, indent and prefix states, then return the buildDescriptorLine function.
     *
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
         * Given a descriptor (member or method), then build the formatted string as a line to output.
         *
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
                $this->generateDescriptorLineBuilder(
                    $descriptor,
                    $descriptorIndent,
                    "{$descriptorKey}\x20{$descriptorPrefix}"
                ),
                $toString
            );
        };
    }

    /**
     * Retrieve all class members and build a sorted array of member declarations.
     *
     * @return array
     */
    private function getClassMembers(): array
    {
        $classVars = array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
        ksort($classVars);
        return array_reduce(array_keys($classVars), $this->generateMemberDeclarationBuilder($classVars), []);
    }

    /**
     * Store all of the class members, and return the buildMemberDeclaration function.
     *
     * @param array $classVars
     *
     * @return callable
     */
    private function generateMemberDeclarationBuilder(array $classVars): callable
    {
        $memberClass = '';
        /**
         * Given a sorted array of member declarations and a specific member name, build a string for the member
         * declaration and add it to the array of members.
         *
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
            if (is_string($memberValue)) {
                $memberValue = '"' . $memberValue . '"';
            }
            if (is_array($memberValue) || is_object($memberValue)) {
                $memberValue = json_encode($memberValue);
            }
            if (!is_null($memberValue)) {
                $memberValue = " = {$memberValue}";
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
            $arrayPointer[$memberKey] = "{$memberKey}{$memberValue}";
            return $members;
        };
    }

    /**
     * Retrieve all class methods and build a sorted array of method declarations.
     *
     * @return array
     */
    private function getClassMethods(): array
    {
        $classMethods = get_class_methods(get_class($this));
        sort($classMethods);
        return array_reduce($classMethods, 'self::buildMethodDeclaration', []);
    }

    /**
     * Given a sorted array of method declarations and a specific method name, build a string for the method
     * declaration and add it to the array of methods.
     *
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
     * Give a string representing a method's parameters, append another parameter.
     *
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
