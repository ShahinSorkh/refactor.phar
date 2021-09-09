<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PHPUnit\Framework\TestCase;
use PhpParser\NodeTraverser;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;

class LocalVariableClassifierTest extends TestCase
{
    /**
     * @test
     */
    public function givenVariable_WhenClassification_ThenLocalVariableFound()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Variable("foo");

        $classifier->enterNode($variable);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenAssignment_WhenClassification_ThenAssignmentFound()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Assign(
            new Variable("foo"),
            new Variable("bar")
        );

        $classifier->enterNode($assign);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }

    /**
     * @test
     */
    public function givenAssignmentAndReadOfSameVariable_WhenClassification_ThenFindBoth()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Assign(
            new Variable("foo"),
            new Variable("foo")
        );

        $traverser     = new NodeTraverser;
        $traverser->addVisitor($classifier);
        $traverser->traverse(array($assign));

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenThisVariable_WhenClassification_ThenNoLocalVariables()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Variable("this");

        $classifier->enterNode($variable);

        $this->assertEquals(array(), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenParam_WhenClassification_FindAsAssignment()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Param("foo");

        $classifier->enterNode($variable);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }

    /**
     * @test
     * @group GH-4
     */
    public function givenArrayDimFetchASsignment_WhenClassification_FindAsAssignmentAndRead()
    {
        $classifier = new LocalVariableClassifier();

        $assign = new Assign(
            new ArrayDimFetch(
                new Variable("foo")
            ),
            new Variable("bar")
        );

        $classifier->enterNode($assign);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }
}
