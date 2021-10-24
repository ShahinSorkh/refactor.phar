<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\RenameLocalVariable;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenameLocalVariableCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rename-local-variable')
            ->setDescription('Rename a local variable inside a method')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains list of statements to extract')
            ->addArgument('line', InputArgument::REQUIRED, 'Line where the local variable is defined.')
            ->addArgument('name', InputArgument::REQUIRED, 'Current name of the variable, with or without the $')
            ->addArgument('new-name', InputArgument::REQUIRED, 'New name of the variable')
            ->setHelp(
                <<<'HELP'
Rename a local variable of a method.

<comment>Operations:</comment>

1. Renames a local variable by giving it a new name inside the method.

<comment>Pre-Conditions:</comment>

1. Check that new variable name does not exist (NOT YET CHECKED).

<comment>Usage:</comment>

    <info>php refactor.phar rename-local-variable file.php 17 hello newHello</info>

Renames <info>$hello</info> in line <info>17</info> of <info>file.php</info> into <info>$newHello</info>.

HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int) $input->getArgument('line');
        $name = new Variable($input->getArgument('name'));
        $newName = new Variable($input->getArgument('new-name'));

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $renameLocalVariable = new RenameLocalVariable($scanner, $codeAnalysis, $editor);
        $renameLocalVariable->refactor($file, $line, $name, $newName);

        return 0;
    }
}
