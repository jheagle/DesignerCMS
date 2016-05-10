<?php

//header("Content-type: application/json");

// Hierarchal order matters here, do not change file load order
require_once $MODELS['StringClass'];
require_once $MODELS['NumberClass'];

abstract class DataType
{
    protected $value;
    protected static $systemMaxBits;

    public function __construct($value)
    {
        $this->systemMaxBits = PHP_INT_SIZE << 3;
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
}
