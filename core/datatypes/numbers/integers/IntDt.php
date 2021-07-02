<?php

namespace Core\DataTypes\Numbers\Integers;

class IntDt extends BigIntDt
{

    protected int $bits = 32;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}
