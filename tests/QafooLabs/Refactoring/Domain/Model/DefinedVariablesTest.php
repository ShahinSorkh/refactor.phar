<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

class DefinedVariablesTest extends TestCase
{
    /**
     * @test
     */
    public function when_variables_used_after__then_return_assignments()
    {
        $selectedRange = new DefinedVariables(['foo' => [1]], ['foo' => [1]]);
        $methodRange = new DefinedVariables(['foo' => [1, 2]], ['foo' => [1, 2]]);

        $variables = $methodRange->variablesFromSelectionUsedAfter($selectedRange);

        $this->assertEquals(['foo'], $variables);
    }
}
