<?php

namespace QafooLabs\Refactoring\Adapters\PatchBuilder;

use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\LineRange;

class PatchBuffer implements EditorBuffer
{
    /** @var \QafooLabs\Patches\PatchBuilder */
    private $builder;

    public function __construct(PatchBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getLines(LineRange $range)
    {
        return $this->builder->getOriginalLines($range->getStart(), $range->getEnd());
    }

    public function replace(LineRange $range, array $newLines)
    {
        $this->builder->replaceLines($range->getStart(), $range->getEnd(), $newLines);
    }

    public function append($line, array $newLines)
    {
        $this->builder->appendToLine($line, $newLines);
    }

    public function replaceString($line, $oldToken, $newToken)
    {
        if ($oldToken === $newToken) {
            return;
        }

        $this->builder->changeToken($line, $oldToken, $newToken);
    }
}
