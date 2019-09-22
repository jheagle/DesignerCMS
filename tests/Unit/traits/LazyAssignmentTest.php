<?php

namespace Core\Tests\Unit\Traits;

use Core\Tests\TestCase;
use Core\Utilities\Traits\LazyAssignment;

/**
 * Class LazyAssignmentTest
 *
 * @package Core\Tests\Unit\Traits
 */
class LazyAssignmentTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function toAllMembersAssigned()
    {
        $this->markTestIncomplete('Coverage for LazyAssignment trait needs to be created.');
        $implementor = $this->buildLazyAssignmentClass();
    }

    private function buildLazyAssignmentClass()
    {
        return new class
        {
            use LazyAssignment;
        };
    }
}
