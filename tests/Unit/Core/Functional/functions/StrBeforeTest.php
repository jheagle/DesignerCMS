<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class StrBeforeTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('strBefore')]
class StrBeforeTest extends TestCase
{
    /**
     * Given a string with one or more occurrences of substring
     * When passing the string and substring to strBefore
     * Then string part before the first substring occurrence will be returned.
     */
    #[Test]
    final public function retrieveFirstPartOfStringBeforeFirstSubstring(): void
    {
        $this->assertEquals(
            'someReally',
            strBefore('someReally.long.dot.notation.string', '.')
        );
        $this->assertEquals(
            'this greeting',
            strBefore('this greeting hello has too many hello', ' hello')
        );
    }
}