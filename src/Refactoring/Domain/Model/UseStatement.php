<?php

namespace QafooLabs\Refactoring\Domain\Model;

class UseStatement
{
    /** @var QafooLabs\Refactoring\Domain\Model\LineRange */
    private $declaredLines;

    /** @var QafooLabs\Refactoring\Domain\Model\File */
    private $file;

    public function __construct(File $file = null, LineRange $declaredLines = null)
    {
        $this->file = $file;
        $this->declaredLines = $declaredLines;
    }

    public function getEndLine()
    {
        return $this->declaredLines->getEnd();
    }
}
