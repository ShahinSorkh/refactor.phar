<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LocalVariableClassifier;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\NodeConnector;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Services\VariableScanner;

class ParserVariableScanner implements VariableScanner
{
    public function scanForVariables(File $file, LineRange $range)
    {
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $stmts = $parser->parse($file->getCode());

        $collector = new LineRangeStatementCollector($range);

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if (!$selectedStatements) {
            throw new \RuntimeException('No statements found in line range.');
        }

        $localVariableClassifier = new LocalVariableClassifier();
        $traverser = new NodeTraverser;
        $traverser->addVisitor($localVariableClassifier);
        $traverser->traverse($selectedStatements);

        $localVariables = $localVariableClassifier->getUsedLocalVariables();
        $assignments = $localVariableClassifier->getAssignments();

        return new DefinedVariables($localVariables, $assignments);
    }
}
