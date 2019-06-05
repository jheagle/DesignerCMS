<?php

namespace Core\DataTypes\Numbers;

class TinyIntDt extends BigIntDt
{

    protected $bits = 8;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}
