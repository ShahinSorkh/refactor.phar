<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\LineRange;

class LineRangeTest extends TestCase
{
    public function test_create_from_single_line()
    {
        $range = LineRange::fromSingleLine(1);

        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(1, $range->getEnd());

        $this->assertTrue($range->isInRange(1));
        $this->assertFalse($range->isInRange(2));
    }

    public function test_create_from_string()
    {
        $range = LineRange::fromString('1-4');

        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(4, $range->getEnd());

        $this->assertTrue($range->isInRange(1));
        $this->assertFalse($range->isInRange(5));
    }

    public function test_create_from_lines()
    {
        $range = LineRange::fromLines(1, 4);

        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(4, $range->getEnd());

        $this->assertTrue($range->isInRange(1));
        $this->assertFalse($range->isInRange(5));
    }
}
