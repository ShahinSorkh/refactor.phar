<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\ConvertLocalToInstanceVariable;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertLocalToInstanceVariableCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('convert-local-to-instance-variable')
            ->setDescription('Convert a local variable to an instance variable.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains the local variable.')
            ->addArgument('line', InputArgument::REQUIRED, 'Line of one of the local variables occurrences.')
            ->addArgument('variable', InputArgument::REQUIRED, 'Name of the variable with or without $.')
            ->setHelp(
                <<<'HELP'
If you want to convert a variable that is local to a method to an instance variable of
that same class, the "convert local to instance variable" refactoring helps you with this
task.

<comment>It will:</comment>

1. Convert all occurrences of the same variable within the method into an instance variable of the same name.
2. Create the instance variable on the class.

<comment>Pre-Conditions:</comment>

1. Selected Variable does not exist on class (NOT CHECKED YET)
2. Variable is a local variable

<comment>Usage:</comment>

    <info>php refactor.phar convert-local-to-instance-variable file.php 10 hello</info>

Will convert variable $hello into an instance variable $this->hello.
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int) $input->getArgument('line');
        $variable = new Variable($input->getArgument('variable'));

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $convertRefactoring = new ConvertLocalToInstanceVariable($scanner, $codeAnalysis, $editor);
        $convertRefactoring->refactor($file, $line, $variable);

        return 0;
    }
}
