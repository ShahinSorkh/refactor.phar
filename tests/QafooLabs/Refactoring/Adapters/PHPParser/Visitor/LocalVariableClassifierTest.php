<?php

namespace Tests\QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LocalVariableClassifier;

class LocalVariableClassifierTest extends TestCase
{
    /**
     * @test
     */
    public function given_variable__when_classification__then_local_variable_found()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Variable('foo');

        $classifier->enterNode($variable);

        $this->assertEquals(['foo' => [-1]], $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function given_assignment__when_classification__then_assignment_found()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Assign(
            new Variable('foo'),
            new Variable('bar')
        );

        $classifier->enterNode($assign);

        $this->assertEquals(['foo' => [-1]], $classifier->getAssignments());
    }

    /**
     * @test
     */
    public function given_assignment_and_read_of_same_variable__when_classification__then_find_both()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Assign(
            new Variable('foo'),
            new Variable('foo')
        );

        $traverser = new NodeTraverser;
        $traverser->addVisitor($classifier);
        $traverser->traverse([$assign]);

        $this->assertEquals(['foo' => [-1]], $classifier->getAssignments());
        $this->assertEquals(['foo' => [-1]], $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function given_this_variable__when_classification__then_no_local_variables()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Variable('this');

        $classifier->enterNode($variable);

        $this->assertEquals([], $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function given_param__when_classification__find_as_assignment()
    {
        $classifier = new LocalVariableClassifier();
        $param = new Param(new Variable('foo'));

        $classifier->enterNode($param);

        $this->assertEquals(['foo' => [-1]], $classifier->getAssignments());
    }

    /**
     * @test
     * @group GH-4
     */
    public function given_array_dim_fetch_a_ssignment__when_classification__find_as_assignment_and_read()
    {
        $classifier = new LocalVariableClassifier();

        $assign = new Assign(
            new ArrayDimFetch(
                new Variable('foo')
            ),
            new Variable('bar')
        );

        $classifier->enterNode($assign);

        $this->assertEquals(['foo' => [-1]], $classifier->getLocalVariables());
        $this->assertEquals(['foo' => [-1]], $classifier->getAssignments());
    }
}
