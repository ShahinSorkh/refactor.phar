<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddProperty;

class AddPropertyTest extends TestCase
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
            new AddProperty(5, 'testProperty')
        );
    }

    public function testPropertyIsAppenedAtGivenLine()
    {
        $line = 27;

        $action = new AddProperty($line, '');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($line, $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testPropertyCodeIsCorrect()
    {
        $action = new AddProperty(5, 'testProperty');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->anything(), $this->equalTo(array(
                '    private $testProperty;',
                ''
              )));

        $action->performEdit($this->buffer);
    }
}
