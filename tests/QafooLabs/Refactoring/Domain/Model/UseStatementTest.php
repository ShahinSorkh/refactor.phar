<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\UseStatement;

class UseStatementTest extends TestCase
{
    private $useStatement;

    public function setUp(): void
    {
        parent::setUp();

        $file = File::createFromPath(__FILE__, __DIR__);
        $this->useStatement = new UseStatement($file, LineRange::fromLines(3, 5));
    }

    public function test_returns_end_line_from_line_range()
    {
        $this->assertEquals(5, $this->useStatement->getEndLine());
    }
}
