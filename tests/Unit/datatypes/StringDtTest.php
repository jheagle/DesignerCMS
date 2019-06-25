<?php

namespace Core\Tests\Unit\DataTypes;

use Core\DataTypes\Strings\StringDt;
use Core\Tests\TestCase;

/**
 * Class StringDtTest
 *
 * @package Core\Tests\Unit\DataTypes
 *
 * @small
 *
 * @group Unit
 * @group DataType
 * @group StringDt
 */
class StringDtTest extends TestCase
{
    /** @test */
    public function createAStringDtInstance()
    {
        $string = new StringDt('some string to test');
        $this->trace('charset')($string->getCharSet());
        $this->tt('instance')($string);
        $this->assertTrue(true);
    }
}
