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

    public function getValue()
    {
        return $this->value;
    }

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

    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
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

    private function applyPropertySettings(array $settings = [])
    {
        // Retrieve all the properties of this class so they can be populated lazily
        foreach (get_class_vars(__CLASS__) as $classMemberName => $default) {
            // Set this property to the incoming form data otherwise, use the default value
            $newClassMemberValue = is_array($default)
                ? $default + $settings[$classMemberName] ?? []
                : $settings[$classMemberName] ?? $default;
            if (isset(self::$$classMemberName)) {
                // Set default static value
                self::$$classMemberName = $newClassMemberValue;
            } else {
                $this->{$classMemberName} = $newClassMemberValue;
            }
        }
    }
}
