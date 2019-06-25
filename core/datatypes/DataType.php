<?php

namespace Core\DataTypes;

/**
 * Class DataType
 *
 * @package Core\DataTypes
 */
abstract class DataType implements DataTypeObject
{

    /** @var mixed $value */
    protected $value;

    /** @var string $primitiveType */
    protected $primitiveType = 'object';

    /** @var int $systemMaxBits */
    protected static $systemMaxBits;

    public function __construct($value, $settings = [])
    {
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        $this->applyPropertySettings($settings);
        $this->value = $value;
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
     * @param mixed|DataType $datatype
     *
     * @return bool
     */
    public function isEqual($datatype): bool
    {
        if (is_a($datatype, 'DataType')) {
            return $this->getValue() === $datatype->getValue();
        }

        return $this->getValue() === $datatype;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = '';
        foreach (get_class_vars(get_class($this)) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            if (is_array($v) || is_object($v)) {
                $string .= "{$k}: " . count((array)$v);
                continue;
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

    /**
     * @param array $settings
     */
    private function applyPropertySettings(array $settings = [])
    {
        // Retrieve all the properties of this class so they can be populated lazily
        foreach (get_class_vars(get_class($this)) as $classMemberName => $default) {
            // Set this property to the incoming form data otherwise, use the default value
            $newClassMemberValue = is_array($default)
                ? $default + $settings[$classMemberName] ?? []
                : $settings[$classMemberName] ?? $default;
            try {
                // Attempt to assign the property statically
                $this::$$classMemberName = $newClassMemberValue;
            } catch (\Error $e) {
                // Failed, must not be statically accessible, assign as instance property
                $this->{$classMemberName} = $newClassMemberValue;
            }
        }
    }
}
