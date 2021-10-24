<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;

class OptimizeUse
{
    private $codeAnalysis;

    private $editor;

    private $phpNameScanner;

    public function __construct($codeAnalysis, $editor, $phpNameScanner)
    {
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
        $this->phpNameScanner = $phpNameScanner;
    }

    public function refactor(File $file)
    {
        $classes = $this->codeAnalysis->findClasses($file);
        $occurances = $this->phpNameScanner->findNames($file);
        $class = $classes[0];

        $appendNewLine = $class->namespaceDeclarationLine() === 0;
        $lastUseStatementLine = $class->namespaceDeclarationLine() + 2;
        $usedNames = [];
        $fqcns = [];

        foreach ($occurances as $occurance) {
            $name = $occurance->name();

            if ($name->type() === PhpName::TYPE_NAMESPACE || $name->type() === PhpName::TYPE_CLASS) {
                continue;
            }

            if ($name->isUse()) {
                $lastUseStatementLine = $occurance->declarationLine();
                $usedNames[] = $name->fullyQualifiedName();
            } elseif ($name->isFullyQualified()) {
                $fqcns[] = $occurance;
            }
        }

        if (!$fqcns) {
            return;
        }

        $buffer = $this->editor->openBuffer($file);

        foreach ($fqcns as $occurance) {
            $name = $occurance->name();
            $buffer->replaceString($occurance->declarationLine(), '\\'.$name->fullyQualifiedName(), $name->shortname());

            if (!in_array($name->fullyQualifiedName(), $usedNames)) {
                $lines = [sprintf('use %s;', $name->fullyQualifiedName())];
                if ($appendNewLine) {
                    $appendNewLine = false;
                    $lines[] = '';
                }

                $buffer->append($lastUseStatementLine, $lines);
                $lastUseStatementLine++;
            }
        }

        $this->editor->save();
    }
}
