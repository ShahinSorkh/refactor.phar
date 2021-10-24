<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Occurance of a name in a specific file+line.
 */
class PhpNameOccurance
{
    /** @var PhpName */
    private $name;

    /** @var File */
    private $file;

    /** @var int */
    private $declarationLine;

    public function __construct(PhpName $name, File $file, $declarationLine)
    {
        $this->name = $name;
        $this->file = $file;
        $this->declarationLine = $declarationLine;
    }

    public function name()
    {
        return $this->name;
    }

    public function declarationLine()
    {
        return $this->declarationLine;
    }

    public function file()
    {
        return $this->file;
    }
}
