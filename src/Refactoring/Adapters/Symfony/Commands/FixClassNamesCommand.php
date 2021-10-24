<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\FixClassNames;
use QafooLabs\Refactoring\Domain\Model\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixClassNamesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fix-class-names')
            ->setDescription('Find all classes whose names don\'t match their required PSR-0 name and rename them.')
            ->addArgument('dir', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Directory that contains the source code to refactor')
            ->setHelp(
                <<<'HELP'
Fix class and namespace names to correspond to the current filesystem layout,
given that the project uses PSR-0. This means you can use this tool to
rename classes and namespaces by renaming folders and files and then applying
the command to fix class and namespaces.

<comment>Operations:</comment>

1. Find all PHP files in given directory.
2. Check every PHP file for class names and namespace definition
3. Change the namespaces and class names to match the current file name

<comment>Pre-Conditions:</comment>

This refactoring has no pre-conditions.

<comment>Usage:</comment>

    <info>php refactor.phar fix-class-names src/</info>
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = new Directory($input->getArgument('dir'), getcwd());

        $codeAnalysis = new StaticCodeAnalysis();
        $phpNameScanner = new ParserPhpNameScanner();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $fixClassNames = new FixClassNames($codeAnalysis, $editor, $phpNameScanner);
        $fixClassNames->refactor($directory);

        return 0;
    }
}
