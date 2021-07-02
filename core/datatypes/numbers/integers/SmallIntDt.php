<?php

namespace Core\DataTypes\Numbers\Integers;

class SmallIntDt extends BigIntDt
{

    protected int $bits = 16;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}