<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\EditingAction\RenameVariable;
use QafooLabs\Refactoring\Domain\Model\Variable;

class RenameVariableTest extends TestCase
{
    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function test_it_is_an_editing_action()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new RenameVariable(
                new DefinedVariables([], []),
                new Variable('testVar'),
                new Variable('newVar')
            )
        );
    }

    public function test_it_replaces_variable_with_instance_variable_version()
    {
        $oldName = new Variable('varName');
        $newName = new Variable('newName');

        $action = new RenameVariable(
            new DefinedVariables(['varName' => [1]], []),
            $oldName,
            $newName
        );

        $this->buffer
            ->expects($this->once())
            ->method('replaceString')
            ->with($this->anything(), $this->equalTo('$varName'), $this->equalTo('$newName'));

        $action->performEdit($this->buffer);
    }

    public function test_it_replaces_on_line_for_read_only_variable()
    {
        $definedVars = new DefinedVariables(['theVar' => [12]], []);
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
            ->expects($this->once())
            ->method('replaceString')
            ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function test_it_replaces_on2_lines_for_read_only_variable()
    {
        $definedVars = new DefinedVariables(['theVar' => [12, 15]], []);
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
            ->expects($this->exactly(2))
            ->method('replaceString')
            ->withConsecutive(
                [$this->equalTo(12), $this->anything(), $this->anything()],
                [$this->equalTo(15), $this->anything(), $this->anything()]
            );

        $action->performEdit($this->buffer);
    }

    public function test_it_replaces_on_line_for_changed_variable()
    {
        $definedVars = new DefinedVariables([], ['theVar' => [12]]);
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
            ->expects($this->once())
            ->method('replaceString')
            ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function test_it_replaces_on2_lines_for_changed_variable()
    {
        $definedVars = new DefinedVariables([], ['theVar' => [12, 15]]);
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
            ->expects($this->exactly(2))
            ->method('replaceString')
            ->withConsecutive(
                [$this->equalTo(12), $this->anything(), $this->anything()],
                [$this->equalTo(15), $this->anything(), $this->anything()]
            );

        $action->performEdit($this->buffer);
    }
}
