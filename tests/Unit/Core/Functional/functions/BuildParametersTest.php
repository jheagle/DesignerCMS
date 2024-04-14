<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

/**
 * Class BuildParametersTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('buildParameters')]
class BuildParametersTest extends TestCase
{
    /**
     * Given an associative array of parameters,
     * When passing this array to the buildParameters function,
     * Then the parameters will be sorted correctly.
     */
    #[Test]
    final public function buildsParametersFromAssociativeArray(): void
    {
        $paramArray = [
            'param4' => [],
            'param5' => false,
            'param3' => 1.1,
            'param1' => 25,
            'param6' => (object)['some' => 'object'],
            'param2' => 'some string',
        ];
        $params = buildParameters(SampleClass::class, 'sampleMethod', $paramArray);
        $sampleResults = (new SampleClass())->sampleMethod(...$params);
        $this->assertEquals($paramArray['param4'], $sampleResults->propertyArray);
        $this->assertEquals($paramArray['param5'], $sampleResults->propertyBoolean);
        $this->assertEquals($paramArray['param3'], $sampleResults->propertyFloat);
        $this->assertEquals($paramArray['param1'], $sampleResults->propertyInteger);
        $this->assertEquals($paramArray['param6'], $sampleResults->propertyObject);
        $this->assertEquals($paramArray['param2'], $sampleResults->propertyString);
    }

    /**
     * Given an array of parameters,
     * When passing this array to the buildParameters function,
     * Then the parameters will used in-place.
     */
    #[Test]
    final public function buildsParametersFromAnArray(): void
    {
        $paramArray = [
            25,
            'some string',
            1.1,
            [],
            false,
            (object)['some' => 'object'],
        ];
        $params = buildParameters(SampleClass::class, 'sampleMethod', $paramArray);
        $sampleResults = (new SampleClass())->sampleMethod(...$params);
        $this->assertEquals($paramArray[0], $sampleResults->propertyInteger);
        $this->assertEquals($paramArray[1], $sampleResults->propertyString);
        $this->assertEquals($paramArray[2], $sampleResults->propertyFloat);
        $this->assertEquals($paramArray[3], $sampleResults->propertyArray);
        $this->assertEquals($paramArray[4], $sampleResults->propertyBoolean);
        $this->assertEquals($paramArray[5], $sampleResults->propertyObject);
    }
}

class SampleClass
{
    public int $propertyInteger;
    public string $propertyString;
    public float $propertyFloat;
    public array $propertyArray;
    public bool $propertyBoolean;
    public object $propertyObject;

    public function __construct()
    {
        $this->propertyInteger = 1;
        $this->propertyString = 'string';
        $this->propertyFloat = 1.1;
        $this->propertyArray = ['array'];
        $this->propertyBoolean = true;
        $this->propertyObject = new stdClass();
    }

    final public function sampleMethod(
        int $param1,
        string $param2,
        float $param3,
        array $param4,
        bool $param5,
        object $param6
    ): self {
        $this->propertyInteger = $param1;
        $this->propertyString = $param2;
        $this->propertyFloat = $param3;
        $this->propertyArray = $param4;
        $this->propertyBoolean = $param5;
        $this->propertyObject = $param6;
        return $this;
    }

}