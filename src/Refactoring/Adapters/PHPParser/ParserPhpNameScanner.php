<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\PhpNameCollector;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;
use QafooLabs\Refactoring\Domain\Services\PhpNameScanner;

class ParserPhpNameScanner implements PhpNameScanner
{
    public function findNames(File $file)
    {
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $collector = new PhpNameCollector();
        $traverser = new NodeTraverser;

        try {
            $stmts = $parser->parse($file->getCode());
        } catch (Error $e) {
            throw new \RuntimeException('Error parsing '.$file->getRelativePath().': '.$e->getMessage(), 0, $e);
        }

        $traverser->addVisitor($collector);
        $traverser->traverse($stmts);

        return array_map(function ($use) use ($file) {
            $type = constant('QafooLabs\Refactoring\Domain\Model\PhpName::TYPE_'.strtoupper($use['type']));

            return new PhpNameOccurance(
                new PhpName(
                    $use['fqcn'],
                    $use['alias'],
                    $type
                ),
                $file,
                $use['line']
            );
        }, $collector->collectedNameDeclarations());
    }
}
