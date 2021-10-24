<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\Line;
use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Utils\ToStringIterator;

class LineCollectionTest extends TestCase
{
    public function test_it_stores_lines()
    {
        $lineObjects = [
            new Line('line 1'),
            new Line('line 2'),
        ];

        $lines = new LineCollection($lineObjects);

        $this->assertSame($lineObjects, $lines->getLines());
    }

    public function test_append_adds_a_line()
    {
        $line1 = new Line('line 1');
        $line2 = new Line('line 2');

        $lines = new LineCollection([$line1]);

        $lines->append($line2);

        $this->assertSame([$line1, $line2], $lines->getLines());
    }

    public function test_append_string_adds_a_line()
    {
        $line1 = 'line 1';
        $line2 = 'line 2';

        $lines = new LineCollection([new Line($line1)]);

        $lines->appendString($line2);

        $this->assertEquals(
            [new Line($line1), new Line($line2)],
            $lines->getLines()
        );
    }

    public function test_create_from_array()
    {
        $lines = LineCollection::createFromArray([
            'line1',
            'line2',
        ]);

        $this->assertEquals(
            [new Line('line1'), new Line('line2')],
            $lines->getLines()
        );
    }

    public function test_create_from_string()
    {
        $lines = LineCollection::createFromString(
            "line1\nline2"
        );

        $this->assertEquals(
            [new Line('line1'), new Line('line2')],
            $lines->getLines()
        );
    }

    public function test_is_iterable()
    {
        $lineObjects = [
            new Line('line 1'),
            new Line('line 2'),
        ];

        $lines = new LineCollection($lineObjects);

        $this->assertEquals($lineObjects, iterator_to_array($lines));
    }

    public function test_append_lines_adds_given_lines()
    {
        $lines = LineCollection::createFromArray([
            'line1',
            'line2',
        ]);

        $lines->appendLines(LineCollection::createFromArray([
            'line3',
            'line4',
        ]));

        $this->assertEquals(
            ['line1', 'line2', 'line3', 'line4'],
            iterator_to_array(new ToStringIterator($lines->getIterator()))
        );
    }

    public function test_appendlank_line()
    {
        $lines = new LineCollection();

        $lines->appendBlankLine();

        $this->assertEquals(
            [''],
            iterator_to_array(new ToStringIterator($lines->getIterator()))
        );
    }
}
