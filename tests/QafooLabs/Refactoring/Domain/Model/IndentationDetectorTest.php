<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\IndentationDetector;
use QafooLabs\Refactoring\Domain\Model\LineCollection;

class IndentationDetectorTest extends TestCase
{
    public function test_get_min_indentation_for_one_line()
    {
        $detector = $this->createDetector(['    echo "test";']);

        $this->assertEquals(4, $detector->getMinIndentation());
    }

    public function test_get_min_indentation_for_first_line()
    {
        $detector = $this->createDetector([
            '  echo "Line 1";',
            '    echo "Line 2";',
        ]);

        $this->assertEquals(2, $detector->getMinIndentation());
    }

    public function test_get_min_intentation_for_later_line()
    {
        $detector = $this->createDetector([
            '    echo "Line 1";',
            '  echo "Line 2";',
        ]);

        $this->assertEquals(2, $detector->getMinIndentation());
    }

    public function test_get_min_indentation_with_blank_lines()
    {
        $detector = $this->createDetector([
            '',
            '    echo "test";',
        ]);

        $this->assertEquals(4, $detector->getMinIndentation());
    }

    public function test_get_first_line_indentation()
    {
        $detector = $this->createDetector([
            '    echo "line 1";',
            '  echo "line 2";',
        ]);

        $this->assertEquals(4, $detector->getFirstLineIndentation());
    }

    public function test_get_first_line_indentation_with_blank_lines()
    {
        $detector = $this->createDetector([
            '',
            '  echo "test";',
        ]);

        $this->assertEquals(2, $detector->getFirstLineIndentation());
    }

    private function createDetector(array $lines)
    {
        return new IndentationDetector(LineCollection::createFromArray($lines));
    }
}
