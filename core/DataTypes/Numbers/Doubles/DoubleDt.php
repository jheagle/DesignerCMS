<?php

namespace Core\DataTypes\Numbers\Doubles;

/**
 * Class DoubleDt
 *
 * @package Core\DataTypes\Numbers
 */
class DoubleDt extends DecimalDt
{

    public function __construct($value = 0, $settings = [])
    {
        parent::__construct($value, $settings);
    }

}
