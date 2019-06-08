<?php

namespace Core\Tests\Unit\Datatypes;

use Core\Tests\TestCase;

class StringDt extends TestCase
{
    /**
     * @test
     */
    public function createAStringDtInstance()
    {
        $string = new StringDt();
    }
}