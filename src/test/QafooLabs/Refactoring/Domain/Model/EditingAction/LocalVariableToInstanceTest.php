<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

class LocalVariableToInstanceTest extends TestCase
{
    private $buffer;

    protected function setUp():void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function testItIsAnEditingAction()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new LocalVariableToInstance(
                new DefinedVariables(array(), array()),
                new Variable('testVar')
            )
        );
    }

    public function testItReplacesVariableWithInstanceVariableVersion()
    {
        $variable = new Variable('varName');

        $action = new LocalVariableToInstance(
            new DefinedVariables(array('varName' => array(1)), array()),
            $variable
        );

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->anything(), $this->equalTo('$varName'), $this->equalTo('$this->varName'));

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOnLineForReadOnlyVariable()
    {
        $definedVars = new DefinedVariables(array('theVar' => array(12)), array());
        $variable = new Variable('theVar');

        $action = new LocalVariableToInstance($definedVars, $variable);

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOn2LinesForReadOnlyVariable()
    {
        $definedVars = new DefinedVariables(array('theVar' => array(12, 15)), array());
        $variable = new Variable('theVar');

        $action = new LocalVariableToInstance($definedVars, $variable);

        $this->buffer
             ->expects($this->exactly(2))
             ->method('replaceString')
             ->withConsecutive(
                 [$this->equalTo(12), $this->anything(), $this->anything()],
                 [$this->equalTo(15), $this->anything(), $this->anything()]
             );

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOnLineForChangedVariable()
    {
        $definedVars = new DefinedVariables(array(), array('theVar' => array(12)));
        $variable = new Variable('theVar');

        $action = new LocalVariableToInstance($definedVars, $variable);

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOn2LinesForChangedVariable()
    {
        $definedVars = new DefinedVariables(array(), array('theVar' => array(12, 15)));
        $variable = new Variable('theVar');

        $action = new LocalVariableToInstance($definedVars, $variable);

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
