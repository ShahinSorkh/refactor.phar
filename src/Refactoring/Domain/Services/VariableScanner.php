<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;

interface VariableScanner
{
    /**
     * Scan a line range within a file for defined variables.
     *
     * @return DefinedVariables
     */
    public function scanForVariables(File $file, LineRange $range);
}
