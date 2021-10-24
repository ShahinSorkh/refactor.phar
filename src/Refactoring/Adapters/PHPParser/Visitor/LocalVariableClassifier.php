<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\NodeVisitorAbstract;

/**
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends NodeVisitorAbstract
{
    private $localVariables = [];

    private $assignments = [];

    private $seenAssignmentVariables;

    public function __construct()
    {
        $this->seenAssignmentVariables = new \SplObjectStorage();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Variable) {
            $this->enterVariableNode($node);
        }

        if ($node instanceof Assign) {
            $this->enterAssignment($node);
        }

        if ($node instanceof Param) {
            $this->enterParam($node);
        }
    }

    private function enterParam($node)
    {
        $this->assignments[$node->var->name][] = $node->getLine();
    }

    private function enterAssignment($node)
    {
        if ($node->var instanceof Variable) {
            $this->assignments[$node->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var);
        } elseif ($node->var instanceof ArrayDimFetch) {
            // $foo[] = "baz" is both a read and a write access to $foo
            $this->localVariables[$node->var->var->name][] = $node->getLine();
            $this->assignments[$node->var->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var->var);
        }
    }

    private function enterVariableNode($node)
    {
        if ($node->name === 'this' || $this->seenAssignmentVariables->contains($node)) {
            return;
        }

        $this->localVariables[$node->name][] = $node->getLine();
    }

    public function getLocalVariables()
    {
        return $this->localVariables;
    }

    public function getUsedLocalVariables()
    {
        return $this->localVariables;
    }

    public function getAssignments()
    {
        return $this->assignments;
    }
}
