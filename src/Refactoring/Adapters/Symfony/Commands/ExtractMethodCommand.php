<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\ExtractMethod;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony Adapter to execute the Extract Method Refactoring.
 */
class ExtractMethodCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract-method')
            ->setDescription('Extract a list of statements into a method.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains list of statements to extract')
            ->addArgument('range', InputArgument::REQUIRED, 'Line Range of statements that should be extracted.')
            ->addArgument('newmethod', InputArgument::REQUIRED, 'Name of the new method.')
            ->setHelp(
                <<<'HELP'
Extract a range of lines from one method into its own method.
This refactoring is usually used during cleanup of code into
single units.

This refactoring automatically detects all necessary inputs and outputs from the
function and generates the argument list and return statement accordingly.

<comment>Operations:</comment>

1. Create a new method containing the selected code.
2. Add a return statement with all variables necessary to make caller work.
3. Pass all arguments to make the method work.

<comment>Pre-Conditions:</comment>

1. Selected code is inside a single method.
2. New Method does not exist (NOT YET CHECKED).

<comment>Usage:</comment>

    <info>php refactor.phar extract-method file.php 10-16 newMethodName</info>

Will extract lines <info>10-16</info> from <info>file.php</info> into a new method called <info>newMethodName</info>.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $range = LineRange::fromString($input->getArgument('range'));
        $newMethodName = $input->getArgument('newmethod');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $extractMethod = new ExtractMethod($scanner, $codeAnalysis, $editor);
        $extractMethod->refactor($file, $range, $newMethodName);

        return 0;
    }
}
