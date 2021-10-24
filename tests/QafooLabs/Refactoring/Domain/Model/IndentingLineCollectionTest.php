<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\IndentingLineCollection;
use QafooLabs\Refactoring\Domain\Model\Line;
use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Utils\ToStringIterator;

class IndentingLineCollectionTest extends TestCase
{
    private $lines;

    protected function setUp(): void
    {
        $this->lines = new IndentingLineCollection();
    }

    public function test_is_a_line_collection()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\LineCollection',
            $this->lines
        );
    }

    public function test_append_adds_indentation()
    {
        $this->lines->addIndentation();

        $this->lines->append(new Line('echo "test";'));

        $this->assertLinesMatch([
            '    echo "test";',
        ]);
    }

    public function test_append_adds_mulitple_indentation()
    {
        $this->lines->append(new Line('echo "line1";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line2";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line3";'));

        $this->assertLinesMatch([
            'echo "line1";',
            '    echo "line2";',
            '        echo "line3";',
        ]);
    }

    public function test_append_removes_indentation()
    {
        $this->lines->append(new Line('echo "line1";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line2";'));
        $this->lines->removeIndentation();
        $this->lines->append(new Line('echo "line3";'));

        $this->assertLinesMatch([
            'echo "line1";',
            '    echo "line2";',
            'echo "line3";',
        ]);
    }

    public function test_append_string_obeys_indentation()
    {
        $this->lines->appendString('echo "line1";');
        $this->lines->addIndentation();
        $this->lines->appendString('echo "line2";');
        $this->lines->removeIndentation();
        $this->lines->appendString('echo "line3";');

        $this->assertLinesMatch([
            'echo "line1";',
            '    echo "line2";',
            'echo "line3";',
        ]);
    }

    public function test_append_lines_obeys_indentation()
    {
        $this->lines->addIndentation();

        $this->lines->appendLines(LineCollection::createFromArray([
            'echo "line1";',
            'echo "line2";',
        ]));

        $this->assertLinesMatch([
            '    echo "line1";',
            '    echo "line2";',
        ]);
    }

    public function test_add_blank_line_contains_no_indentation()
    {
        $this->lines->appendBlankLine();

        $this->assertLinesMatch(['']);
    }

    private function assertLinesMatch(array $expected)
    {
        $this->assertEquals(
            $expected,
            iterator_to_array(new ToStringIterator($this->lines->getIterator()))
        );
    }
}
