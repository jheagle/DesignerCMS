<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class StrAfterTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group strAfter
 */
class StrAfterTest extends TestCase
{
    /**
     * Given a string with one or more occurrences of substring
     * When passing the string and substring to strAfter
     * Then string part after the first substring occurrence will be returned.
     *
     * @test
     */
    final public function retrieveLastPartOfStringAfterFirstSubstring(): void
    {
        $this->assertEquals(
            'long.dot.notation.string',
            strAfter('someReally.long.dot.notation.string', '.')
        );
        $this->assertEquals(
            ' has too many hello',
            strAfter('this greeting hello has too many hello', ' hello')
        );
    }
}