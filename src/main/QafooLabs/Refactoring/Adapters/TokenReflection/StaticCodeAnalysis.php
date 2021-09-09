<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */


namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use PhpParser\NodeTraverser;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeNodeCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpClass;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use function Psy\sh;

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
            throw new \InvalidArgumentException("Could not find method end line.");
        }

        return $method->getEndLine();
    }

    public function getMethodStartLine(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        if ($method === null) {
            throw new \InvalidArgumentException("Could not find method start line.");
        }

        return $method->getStartLine();
    }

    public function getLineOfLastPropertyDefinedInScope(File $file, $lastLine)
    {
        $ast = $this->parser->parse($file->getCode());

        // foreach ($file->getNamespaces() as $namespace) {
        //     foreach ($namespace->getClasses() as $class) {
        //         $lastPropertyDefinitionLine = $class->getStartLine() + 1;

        //         foreach ($class->getMethods() as $method) {
        //             if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
        //                 foreach ($class->getProperties() as $property) {
        //                     $lastPropertyDefinitionLine = max($lastPropertyDefinitionLine, $property->getEndLine());
        //                 }

        //                 return $lastPropertyDefinitionLine;
        //             }
        //         }
        //     }
        // }

        throw new \InvalidArgumentException("Could not find method start line.");
    }

    public function isInsideMethod(File $file, LineRange $range)
    {
        return (bool) $this->findMatchingMethod($file, $range);
    }

    /**
     * @param File $file
     * @return PhpClass[]
     */
    public function findClasses(File $file)
    {

        $classes = array();

        foreach ($this->parser->parse($file->getCode()) as $node) {
            if ($node instanceof Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Class_) {
                        $classes[] = new PhpClass(
                            PhpName::createDeclarationName($stmt->name),
                            $stmt->getStartLine(),
                            $node->getStartLine()
                        );
                    }
                }
            } elseif ($node instanceof Class_) {
                $classes[] = new PhpClass(
                    PhpName::createDeclarationName($node->name),
                    $node->getStartLine(),
                    0
                );
            }
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

        return null;
    }
}
