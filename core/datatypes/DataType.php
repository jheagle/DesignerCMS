<?php

namespace Core\DataTypes;

/**
 * Class DataType
 *
 * @package Core\DataTypes
 */
abstract class DataType implements DataTypeObject
{

    const PRIMITIVE_ARRAY = 'array';
    const PRIMITIVE_BOOLEAN = 'boolean';
    const PRIMITIVE_CALLABLE = 'callable';
    const PRIMITIVE_FLOAT = 'float';
    const PRIMITIVE_INTEGER = 'integer';
    const PRIMITIVE_ITERABLE = 'iterable';
    const PRIMITIVE_NULL = 'null';
    const PRIMITIVE_OBJECT = 'object';
    const PRIMITIVE_RESOURCE = 'resource';
    const PRIMITIVE_STRING = 'string';

    const PRIMITIVES = [
        self::PRIMITIVE_BOOLEAN,
        self::PRIMITIVE_FLOAT,
        self::PRIMITIVE_INTEGER,
        self::PRIMITIVE_STRING,
        self::PRIMITIVE_ARRAY,
        self::PRIMITIVE_CALLABLE,
        self::PRIMITIVE_ITERABLE,
        self::PRIMITIVE_OBJECT,
        self::PRIMITIVE_NULL,
        self::PRIMITIVE_RESOURCE,
    ];

    const SCALARS = [
        self::PRIMITIVE_BOOLEAN,
        self::PRIMITIVE_FLOAT,
        self::PRIMITIVE_INTEGER,
        self::PRIMITIVE_STRING,
    ];

    const COMPOUNDS = [
        self::PRIMITIVE_ARRAY,
        self::PRIMITIVE_CALLABLE,
        self::PRIMITIVE_ITERABLE,
        self::PRIMITIVE_OBJECT,
    ];

    const SPECIALS = [
        self::PRIMITIVE_NULL,
        self::PRIMITIVE_RESOURCE,
    ];

    /** @var mixed $value */
    protected $value;

    /** @var string $primitiveType */
    protected $primitiveType = 'object';

    /** @var int $systemMaxBits */
    protected static $systemMaxBits;

    public function __construct($value, $settings = [])
    {
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        $this->applyMemberSettings($settings);
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getSystemMaxBits(): int
    {
        return self::$systemMaxBits;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function setValue($value)
    {
        return $this->value = $value;
    }

    /**
     * @return string
     */
    public function getPrimitiveType(): string
    {
        return $this->primitiveType;
    }

    /**
     * @param mixed|DataType $datatype
     *
     * @return bool
     */
    public function isEqual($datatype): bool
    {
        return is_a($datatype, DataType::class)
            ? $this->getValue() === $datatype->getValue()
            : $this->getValue() === $datatype;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getClassDescription();
    }

    final public function getClassDescription(): string
    {
        $classDescriptors = array_merge_recursive($this->getClassMembers(), $this->getClassMethods());
        return "\n\n\e[1;32m" . get_class($this) . "\x20{\e[0m" . array_reduce(
                array_keys($classDescriptors),
                function (string $toString, string $class) use ($classDescriptors): string {
                    $classIndent = "\e[1;";
                    if ($class !== get_class($this)) {
                        $toString .= "\n\n\x20\x20\e[0;32m{$class}\e[0m";
                        $classIndent = "\x20\x20\e[0;";
                    }
                    $descriptorTypes = $classDescriptors[$class];
                    return array_reduce(array_keys($descriptorTypes),
                        function (string $toString, string $descriptorType) use (
                            $descriptorTypes,
                            $classIndent
                        ): string {
                            $toString .= "\n";
                            $descriptorIndent = $classIndent . ($descriptorType === 'members' ? "35m" : "36m");
                            $descriptors = $descriptorTypes[$descriptorType];
                            return array_reduce($descriptors,
                                function (string $toString, string $descriptor) use ($descriptorIndent) {
                                    $toString .= "\n\x20\x20\x20\x20{$descriptorIndent}{$descriptor}\e[0m";
                                    return $toString;
                                }, $toString);
                        }, $toString);
                },
                ''
            ) . "\n\e[1;32m}\e[0m\n";
    }

    private function getClassMembers(): array
    {
        $classVars = array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
        return array_reduce(array_keys($classVars), function (array $members, string $memberKey): array {
            static $memberClass = '';
            $member = new \ReflectionProperty($this, $memberKey);
            if ($member->class !== $memberClass) {
                $memberClass = $member->class;
                $members[$memberClass]['members'] = [];
            }
            $memberValue = $this->getMember($memberKey);
            switch (gettype($memberValue)) {
                case self::PRIMITIVE_ARRAY:
                case self::PRIMITIVE_OBJECT:
                    $memberValue = json_encode($memberValue);
                    break;
                case self::PRIMITIVE_STRING:
                    $memberValue = '"' . $memberValue . '"';
                    break;
            }
            $visibility = $member->isPublic()
                ? 'public '
                : ($member->isProtected() ? 'protected ' : ($member->isPrivate() ? 'private ' : ''));
            $static = $member->isStatic() ? 'static ' : '';
            $members[$memberClass]['members'][$memberKey] = "{$visibility}{$static}{$memberKey} = {$memberValue}";
            return $members;
        }, []);
    }

    private function getClassMethods(): array
    {
        $classMethods = get_class_methods(get_class($this));
        return array_reduce($classMethods, function (array $methods, string $methodName): array {
            static $methodClass = '';
            $method = new \ReflectionMethod($this, $methodName);
            if ($method->class !== $methodClass) {
                $methodClass = $method->class;
                $members[$methodClass]['methods'] = [];
            }
            $method->getReturnType();
            $parameters = array_reduce($method->getParameters(),
                function (string $paramString, \ReflectionParameter $param): string {
                    $paramDefault = $param->isDefaultValueAvailable() ? ' = ' . json_encode($param->getDefaultValue()) : '';
                    $paramType = $param->hasType() ? $param->getType() . ' ' : '';
                    $paramDescription = $paramType . $param->getName() . $paramDefault;
                    $paramString .= $paramString ? ', ' . $paramDescription : $paramDescription;
                    return $paramString;
                }, '');
            $abstractFinal = $method->isAbstract() ? 'abstract ' : ($method->isFinal() ? 'final ' : '');
            $visibility = $method->isPublic()
                ? 'public '
                : ($method->isProtected() ? 'protected ' : ($method->isPrivate() ? 'private ' : ''));
            $static = $method->isStatic() ? 'static ' : '';
            $returnType = $method->hasReturnType() ? ': ' . $method->getReturnType() : '';
            $methods[$methodClass]['methods'][$methodName] = "{$abstractFinal}{$visibility}{$static}{$methodName}({$parameters}){$returnType}";
            return $methods;
        }, []);
    }

    private function getMember($memberKey)
    {
        try {
            // Attempt to retrieve the member statically
            return $this::$$memberKey;
        } catch (\Error $e) {
            // Failed, must not be statically accessible, retrieve as instance member
            return $this->{$memberKey};
        }
    }

    private function setMember($memberKey, $value)
    {
        try {
            // Attempt to assign the member statically
            $this::$$memberKey = $value;
        } catch (\Error $e) {
            // Failed, must not be statically accessible, assign as instance member
            $this->{$memberKey} = $value;
        }
    }

    /**
     * @param array $settings
     */
    private function applyMemberSettings(array $settings = [])
    {
        $classVars = array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
        // Retrieve all the members of this class so they can be populated lazily
        foreach ($classVars as $classMemberName => $default) {
            // Set this member to the incoming form data otherwise, use the default value
            $newClassMemberValue = is_array($default)
                ? $default + $settings[$classMemberName] ?? []
                : $settings[$classMemberName] ?? $default;
            $this->setMember($classMemberName, $newClassMemberValue);
        }
    }
}
