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


namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\NodeVisitorAbstract;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr\FuncCall;
use function Psy\sh;

/**
 * Given a line range, collect the AST slice that is inside that range.
 */
class LineRangeNodeCollector extends NodeVisitorAbstract
{
    /**
     * @var LineRange
     */
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
            $node->setAttribute('parent', $this->stack[count($this->stack)-1]);
        }
        $this->stack[] = $node;

        if ( ! $this->lineRange->isInRange($node->getLine())) {
            return;
        }

        $this->nodes->attach($node->getAttribute('parent'));
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

