<?php

namespace Tests\QafooLabs\Refactoring\Application;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Application\RenameLocalVariable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;

class RenameLocalVariableTest extends TestCase
{
    private $scanner;

    private $codeAnalysis;

    private $editor;

    private $refactoring;

    public function setUp(): void
    {
        $this->scanner = \Phake::mock('QafooLabs\Refactoring\Domain\Services\VariableScanner');
        $this->codeAnalysis = \Phake::mock('QafooLabs\Refactoring\Domain\Services\CodeAnalysis');
        $this->editor = \Phake::mock('QafooLabs\Refactoring\Domain\Services\Editor');
        $this->refactoring = new RenameLocalVariable($this->scanner, $this->codeAnalysis, $this->editor);

        \Phake::when($this->codeAnalysis)->isInsideMethod(\Phake::anyParameters())->thenReturn(true);
    }

    public function test_rename_local_variable()
    {
        $buffer = \Phake::mock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        \Phake::when($this->scanner)->scanForVariables(\Phake::anyParameters())->thenReturn(
            new DefinedVariables(['helloWorld' => [6]])
        );
        \Phake::when($this->editor)->openBuffer(\Phake::anyParameters())->thenReturn($buffer);
        \Phake::when($this->codeAnalysis)->findMethodRange(\Phake::anyParameters())->thenReturn(LineRange::fromSingleLine(1));

        $patch = $this->refactoring->refactor(new File(
            'foo.php',
            <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        $helloWorld = 'bar';
    }
}
PHP
        ), 6, new Variable('$helloWorld'), new Variable('$var'));

        \Phake::verify($buffer)->replaceString(6, '$helloWorld', '$var');
    }

    public function test_rename_non_local_variable__throws_exception()
    {
        $this->expectException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Given variable "$this->foo" is required to be local to the current method.');

        $this->refactoring->refactor(
            new File('foo.php', ''),
            6,
            new Variable('$this->foo'),
            new Variable('$foo')
        );
    }

    public function test_rename_into_non_local_variable__throws_exception()
    {
        $this->expectException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Given variable "$this->foo" is required to be local to the current method.');

        $this->refactoring->refactor(
            new File('foo.php', ''),
            6,
            new Variable('$foo'),
            new Variable('$this->foo')
        );
    }
}
