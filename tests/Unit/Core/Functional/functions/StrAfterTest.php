<?php

namespace Tests\Unit\Core\Functional\functions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class StrAfterTest
 * @package Tests\Unit\Core\Functional\functions
 */
#[Group('Unit')]
#[Group('Functional')]
#[Group('Pure')]
#[Group('strAfter')]
class StrAfterTest extends TestCase
{
    /**
     * Given a string with one or more occurrences of substring
     * When passing the string and substring to strAfter
     * Then string part after the first substring occurrence will be returned.
     */
    #[Test]
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