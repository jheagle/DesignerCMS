<?php

namespace DesignerCms\Models\Entity;

use DesignerCms\Models\Core\Number_DT;
use DesignerCms\Models\Core\Potential;

class Field implements Potential
{

    const PRIMARY_KEY = 1;

    const NOT_NULL = 2;

    const UNIQUE = 4;

    const BINARY = 8;

    const UNSIGNED = 16;

    const ZERO_FILL = 32;

    const AUTO_INCREMENT = 64;

    const REQUIRED = 128;

    const INDEX = 256;

    const FULLTEXT = 512;

    protected $name;

    protected $dataType;

    protected $attributes;

    protected $default;

    public function __construct(
        $name = '',
        $dataType = 'String',
        $default = '',
        $length = null,
        $attributes = self::NOT_NULL
    ) {
        $this->name = strtolower(str_replace(' ', '_', $name));
        $this->attributes = $attributes & self::PRIMARY_KEY || $attributes & self::UNIQUE ? $attributes | self::REQUIRED : $attributes;
        if ($this->hasAttr(self::ZERO_FILL)) {
            $this->attributes |= self::UNSIGNED;
        }
        $dataTypeClassName = '\DesignerCms\Models\Core\\' . ucwords(strtolower($dataType)) . '_DT';
        $this->dataType = new $dataTypeClassName(
            $default,
            ['length' => $length, 'isSigned' => !$this->hasAttr(self::UNSIGNED)]
        );
        $this->default = $this->dataType->getValue();
    }

    public function getValue()
    {
        if ($this->dataType instanceof Number_DT && property_exists(
                get_class($this->dataType),
                'length'
            ) && $this->hasAttr(self::ZERO_FILL)) {
            return $this->dataType->getPaddedValue();
        }

        return $this->dataType->getValue();
    }

    public function setValue($value)
    {
        return $this->dataType->setValue($value);
    }

    public function hasAttr($attr)
    {
        return ($this->attributes & $attr) === $attr;
    }

    public function hasAttribute($attr)
    {
        return self::hasAttr($attr);
    }

    public function __toString()
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}
