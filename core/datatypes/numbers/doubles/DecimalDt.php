<?php

namespace Core\DataTypes\Numbers;

/**
 * Class DecimalDt
 *
 * @package Core\DataTypes\Numbers
 */
class DecimalDt extends NumberDt
{

    protected $precision;

    protected $scale;

    /**
     *
     * @param mixed|number $value
     * @param array $settings
     */
    public function __construct($value = 0, array $settings = [])
    {
        parent::__construct($value, $settings);
        $settings = array_merge(
            [
                'precision' => 65,
                'scale' => 50,
                'length' => 0,
                'isSigned' => true,
            ],
            $settings
        );
        self::setPrecision($settings['precision']);
        self::setScale($settings['scale']);
        self::setValue($this->value);
    }

    /**
     *
     * @param int $precision
     */
    protected function setPrecision(int $precision)
    {
        if ($precision < 0) {
            $precision = 0;
        } elseif ($precision > 65) {
            $precision = 65;
        }
        $this->precision = $precision;
    }

    /**
     *
     * @param int $scale
     */
    protected function setScale(int $scale)
    {
        if ($scale < 0) {
            $scale = 0;
        } elseif ($scale > $this->precision) {
            $scale = $this->precision;
        } elseif ($scale > 65) {
            $scale = 65;
        }
        $this->scale = $scale;
    }

    /**
     *
     * @return number
     */
    public function getValue()
    {
        return parent::getValue();
    }

    /**
     *
     * @param number $value
     *
     * @return number
     */
    public function setValue($value)
    {
        $this->isNegative = strstr($value, '-') && $this->isSigned;
        $number = parent::setValue($value);
        $numParts = explode('.', (string)$number);
        if (count($numParts) < 2) {
            $numParts[1] = '0';
        }
        if (strlen($numParts[0]) < 1) {
            $numParts[0] = '0';
        }
        $valueChange = strlen($numParts[1]) > $this->scale;
        $numParts[1] = substr($numParts[1], 0, $this->scale);

        $removeDigits = strlen($numParts[0]) + strlen(
                $numParts[1]
            ) - $this->precision;
        if ($removeDigits > 0) {
            $valueChange = true;
            $numParts[0] = substr($numParts[0], $removeDigits);
        }

        return parent::setValue($prefix . implode('.', $numParts));
    }

}
