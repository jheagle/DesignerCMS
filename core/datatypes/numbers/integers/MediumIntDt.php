<?php

namespace Core\DataTypes\Numbers;

class MediumIntDt extends BigIntDt
{

    protected $bits = 24;

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}