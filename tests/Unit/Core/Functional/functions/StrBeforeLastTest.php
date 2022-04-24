<?php

namespace Tests\Unit\Core\Functional\functions;

use Tests\TestCase;

/**
 * Class StrBeforeLastTest
 * @package Tests\Unit\Core\Functional\functions
 *
 * @group Unit
 * @group Functional
 * @group Pure
 * @group strBeforeLast
 */
class StrBeforeLastTest extends TestCase
{
    /**
     * Given a string with one or more occurrences of substring
     * When passing the string and substring to strBeforeLast
     * Then string part before the last substring occurrence will be returned.
     *
     * @test
     */
    final public function retrieveFirstPartOfStringBeforeLastSubstring(): void
    {
        $this->assertEquals(
            'someReally.long.dot.notation',
            strBeforeLast('someReally.long.dot.notation.string', '.')
        );
        $this->assertEquals(
            'this greeting hello has too many',
            strBeforeLast('this greeting hello has too many hello', ' hello')
        );
    }
}