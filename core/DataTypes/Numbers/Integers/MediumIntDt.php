<?php

namespace Core\DataTypes\Numbers\Integers;

class MediumIntDt extends BigIntDt
{

    protected int $bits = 24;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}