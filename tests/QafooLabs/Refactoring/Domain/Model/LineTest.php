<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\Line;

class LineTest extends TestCase
{
    public function test_it_stores_the_line_of_code()
    {
        $content = 'echo "Hello world!";';

        $line = new Line($content);

        $this->assertEquals($content, (string) $line);
    }

    public function test_is_empty_for_empty_line()
    {
        $line = new Line('');

        $this->assertTrue($line->isEmpty());
    }

    public function test_is_empty_for_line_with_content()
    {
        $line = new Line('$a = 5;');

        $this->assertFalse($line->isEmpty());
    }

    public function test_get_indentation_for2_spaces()
    {
        $line = new Line('  echo "Test";');

        $this->assertEquals(2, $line->getIndentation());
    }

    public function test_get_indentation_for4_spaces()
    {
        $line = new Line('    echo "Test";');

        $this->assertEquals(4, $line->getIndentation());
    }
}
