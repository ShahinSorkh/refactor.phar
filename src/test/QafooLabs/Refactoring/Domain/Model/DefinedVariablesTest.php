<?php

namespace QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;

class DefinedVariablesTest extends TestCase
{
    /**
     * @test
     */
    public function whenVariablesUsedAfter_ThenReturnAssignments()
    {
        $selectedRange = new DefinedVariables(array('foo' => array(1)), array('foo' => array(1)));
        $methodRange = new DefinedVariables(array('foo' => array(1, 2)), array('foo' => array(1, 2)));

        $variables = $methodRange->variablesFromSelectionUsedAfter($selectedRange);

        $this->assertEquals(array('foo'), $variables);
    }
}
