<?php

namespace Core\DataTypes\Numbers\Integers;

class TinyIntDt extends BigIntDt
{

    protected int $bits = 8;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}
