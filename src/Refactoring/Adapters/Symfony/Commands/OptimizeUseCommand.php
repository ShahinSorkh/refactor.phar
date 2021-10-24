<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\OptimizeUse;
use QafooLabs\Refactoring\Domain\Model\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony Adapter to execute the Optimize Use Refactoring.
 */
class OptimizeUseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('optimize-use')
            ->setDescription('Optimize use statements of a file. Replace FQNs with imported aliases.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains the use statements to optimize')
            ->setHelp(
                <<<'HELP'
Optimizes the use of Fully qualified names in a file so that FQN is imported with
"use" at the top of the file and the FQN is replaced with its classname.

All other use statements will be untouched, only new ones will be added.

<comment>Operations:</comment>

1. import found FQNs
2. replace FQNs with the imported classname

<comment>Pre-Conditions:</comment>

1. File has a single namespace defined

<comment>Known issues:</comment>

1. a FQN might be renamed with an conflicting name when the className of the renamend full qualified name is already in use
2. if there is no use statement in the whole file, new ones will be appended after the namespace

<comment>Usage:</comment>

    <info>php refactor.phar optimize-use file.php</info>

Will optimize the use statements in <info>file.php</info>.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());

        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));
        $phpNameScanner = new ParserPhpNameScanner();

        $optimizeUse = new OptimizeUse($codeAnalysis, $editor, $phpNameScanner);
        $optimizeUse->refactor($file);

        return 0;
    }
}
