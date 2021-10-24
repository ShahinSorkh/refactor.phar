<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddProperty;

class AddPropertyTest extends TestCase
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
            new AddProperty(5, 'testProperty')
        );
    }

    public function test_property_is_appened_at_given_line()
    {
        $line = 27;

        $action = new AddProperty($line, '');

        $this->buffer
            ->expects($this->once())
            ->method('append')
            ->with($line, $this->anything());

        $action->performEdit($this->buffer);
    }

    public function test_property_code_is_correct()
    {
        $action = new AddProperty(5, 'testProperty');

        $this->buffer
            ->expects($this->once())
            ->method('append')
            ->with($this->anything(), $this->equalTo([
                '    private $testProperty;',
                '',
            ]));

        $action->performEdit($this->buffer);
    }
}
