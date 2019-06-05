<?php

namespace Core\DataTypes\Numbers;

class SmallIntDt extends BigIntDt
{

    protected $bits = 16;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}