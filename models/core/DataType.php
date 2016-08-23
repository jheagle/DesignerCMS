<?php

// Add add subtypes at bottom

interface DataTypeObject extends Potential {

    public function getValue();

    public function setValue($value);
}

abstract class DataType implements DataTypeObject {

    protected $value;
    protected static $systemMaxBits;

    public function __construct($value) {
        self::$systemMaxBits = PHP_INT_SIZE << 3;
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        return $this->value = $value;
    }

    public function __toString() {
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

// Hierarchal order matters here, do not change file load order
require_once $CORE['String'];
require_once $CORE['Number'];
