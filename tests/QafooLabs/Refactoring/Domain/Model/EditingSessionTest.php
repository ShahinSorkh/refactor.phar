<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

class EditingSessionTest extends TestCase
{
    private $session;

    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        $this->session = new EditingSession($this->buffer);
    }

    public function test_edit_actions_are_performed()
    {
        $action1 = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditingAction');
        $action2 = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditingAction');

        $action1->expects($this->once())
            ->method('performEdit')
            ->with($this->equalTo($this->buffer));

        $action2->expects($this->once())
            ->method('performEdit')
            ->with($this->equalTo($this->buffer));

        $this->session->addEdit($action1);
        $this->session->addEdit($action2);

        $this->session->performEdits();
    }
}
