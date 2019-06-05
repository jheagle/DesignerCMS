<?php

namespace Core\DataTypes\Numbers;

class IntDt extends BigIntDt
{

    protected $bits = 32;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}
