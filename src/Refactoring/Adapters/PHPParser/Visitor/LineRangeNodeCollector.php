<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * Given a line range, collect the AST slice that is inside that range.
 */
class LineRangeNodeCollector extends NodeVisitorAbstract
{
    /** @var LineRange */
    private $lineRange;

    private $nodes;

    private $stack;

    public function __construct(LineRange $lineRange)
    {
        $this->lineRange = $lineRange;
        $this->nodes = new \SplObjectStorage();
    }

    public function beginTraverse(array $nodes)
    {
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }
        $this->stack[] = $node;

        if (!$this->lineRange->isInRange($node->getLine()) || !$node->hasAttribute('parent')) {
            return;
        }

        do {
            $parent = ($parent ?? $node)->getAttribute('parent');
            $this->nodes->attach($parent);
        } while ($parent->hasAttribute('parent'));
    }

    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
    }

    public function getNodes()
    {
        return iterator_to_array($this->nodes);
    }
}
