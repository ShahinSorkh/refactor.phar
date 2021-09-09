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

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Param;
use SplObjectStorage;

/**
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends NodeVisitorAbstract
{
    private $localVariables = array();
    private $assignments = array();
    private $seenAssignmentVariables;

    public function __construct()
    {
        $this->seenAssignmentVariables = new SplObjectStorage();
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
        $this->assignments[$node->var][] = $node->getLine();
    }

    private function enterAssignment($node)
    {
        if ($node->var instanceof Variable) {
            $this->assignments[$node->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var);
        } else if ($node->var instanceof ArrayDimFetch) {
            // $foo[] = "baz" is both a read and a write access to $foo
            $this->localVariables[$node->var->var->name][] = $node->getLine();
            $this->assignments[$node->var->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var->var);
        }
    }

    private function enterVariableNode($node)
    {
        if ($node->name === "this" || $this->seenAssignmentVariables->contains($node)) {
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
