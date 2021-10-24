<?php

namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeNodeCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\PhpNameCollector;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\PhpClass;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;

class StaticCodeAnalysis extends CodeAnalysis
{
    /** @var \PhpParser\Parser\Php7 */
    private $parser;

    /** @var \PhpParser\NodeTraverser */
    private $traverser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser;
    }

    public function isMethodStatic(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        return $method ? $method->isStatic() : false;
    }

    public function getMethodEndLine(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        if ($method === null) {
            throw new \InvalidArgumentException('Could not find method end line.');
        }

        return $method->getEndLine();
    }

    public function getMethodStartLine(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        if ($method === null) {
            throw new \InvalidArgumentException('Could not find method start line.');
        }

        return $method->getStartLine();
    }

    public function getLineOfLastPropertyDefinedInScope(File $file, $lastLine)
    {
        $collector = new LineRangeNodeCollector(LineRange::fromLines(1, $lastLine));
        $this->traverser->addVisitor($collector);
        $ast = $this->parser->parse($file->getCode());
        $this->traverser->traverse($ast);

        $lastPropertyLine = 0;
        foreach ($collector->getNodes() as $node) {
            if ($lastPropertyLine === 0 && $node instanceof Class_) {
                $lastPropertyLine = $node->getStartLine() + 1;
            }

            if ($node instanceof Property) {
                $lastPropertyLine = max($lastPropertyLine, $node->getStartLine());
            }
        }

        if ($lastPropertyLine) {
            return $lastPropertyLine;
        }

        throw new \InvalidArgumentException('Could not find method start line.');
    }

    public function isInsideMethod(File $file, LineRange $range)
    {
        return (bool) $this->findMatchingMethod($file, $range);
    }

    /**
     * @return PhpClass[]
     */
    public function findClasses(File $file)
    {
        $classes = [];
        $ast = $this->parser->parse($file->getCode());
        $collector = new PhpNameCollector;
        $this->traverser->addVisitor($collector);
        $this->traverser->traverse($ast);

        foreach ($collector->collectedNameDeclarations() as $node) {
            if ($node['type'] !== 'class') {
                continue;
            }

            $namespace = $collector->namespaceOfClass($node);
            $classes[] = new PhpClass(
                PhpName::createDeclarationName($node['fqcn']),
                $node['line'],
                $namespace ? $namespace['line'] : 0
            );
        }

        return $classes;
    }

    private function findMatchingMethod(File $file, LineRange $range)
    {
        $collector = new LineRangeNodeCollector($range);
        $ast = $this->parser->parse($file->getCode());
        $this->traverser->addVisitor($collector);
        $this->traverser->traverse($ast);

        foreach ($collector->getNodes() as $node) {
            if ($node instanceof FunctionLike) {
                return $node;
            }
        }
    }
}
