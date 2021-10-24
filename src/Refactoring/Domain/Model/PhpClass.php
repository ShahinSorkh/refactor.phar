<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Representation of a PHP class.
 */
class PhpClass
{
    /** @var PhpName */
    private $declarationName;

    /** @var int */
    private $declarationLine;

    /** @var int */
    private $namespaceDeclarationLine;

    public function __construct(PhpName $declarationName, $declarationLine, $namespaceDeclarationLine)
    {
        $this->declarationName = $declarationName;
        $this->declarationLine = $declarationLine;
        $this->namespaceDeclarationLine = $namespaceDeclarationLine;
    }

    /**
     * PhpName for the declaration of this class.
     *
     * @return PhpName
     */
    public function declarationName()
    {
        return $this->declarationName;
    }

    public function declarationLine()
    {
        return $this->declarationLine;
    }

    public function namespaceDeclarationLine()
    {
        return $this->namespaceDeclarationLine;
    }
}
