<?php

namespace QafooLabs\Refactoring\Adapters\Symfony;

use QafooLabs\Refactoring\Adapters\PatchBuilder\ApplyPatchCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Print Patch to Symfony Console Output.
 */
class OutputPatchCommand implements ApplyPatchCommand
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @var string
     *
     * @param mixed $patch
     */
    public function apply($patch)
    {
        if (empty($patch)) {
            return;
        }

        $this->output->writeln($patch);
    }
}
