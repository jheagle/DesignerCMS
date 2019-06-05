<?php

namespace Core\DataTypes\Numbers;

/**
 * Class FloatDt
 *
 * @package Core\DataTypes\Numbers
 */
class FloatDt extends DecimalDt
{

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}