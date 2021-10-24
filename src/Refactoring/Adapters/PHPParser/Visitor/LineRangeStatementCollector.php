<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * Given a line range, collect the AST slice that is inside that range.
 */
class LineRangeStatementCollector extends NodeVisitorAbstract
{
    /** @var LineRange */
    private $lineRange;

    private $statements;

    public function __construct(LineRange $lineRange)
    {
        $this->lineRange = $lineRange;
        $this->statements = new \SplObjectStorage();
    }

    public function enterNode(Node $node)
    {
        if (!$this->lineRange->isInRange($node->getLine())) {
            return;
        }

        $parent = $node->getAttribute('parent');

        // TODO: Expensive (?)
        do {
            if ($parent && $this->statements->contains($parent)) {
                return;
            }
        } while ($parent && $parent = $parent->getAttribute('parent'));

        $this->statements->attach($node);
    }

    public function getStatements()
    {
        return iterator_to_array($this->statements);
    }
}
