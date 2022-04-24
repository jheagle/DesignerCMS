<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class StrAfterLastTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group strAfterLast
 */
class StrAfterLastTest extends TestCase
{
    /**
     * Given a string with one or more occurrences of substring
     * When passing the string and substring to strAfterLast
     * Then string part after the last substring occurrence will be returned.
     *
     * @test
     */
    final public function retrieveLastPartOfStringAfterLastSubstring(): void
    {
        $this->assertEquals(
            'string',
            strAfterLast('someReally.long.dot.notation.string', '.')
        );
        $this->assertEquals(
            '',
            strAfterLast('this greeting hello has too many hello', ' hello')
        );
    }
}