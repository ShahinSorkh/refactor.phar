<?php

namespace QafooLabs\Refactoring\Domain\Model;

class EditingSession
{
    /** @var EditorBuffer */
    private $buffer;

    /** @var EditingAction[] */
    private $actions = [];

    public function __construct(EditorBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    public function addEdit(EditingAction $action)
    {
        $this->actions[] = $action;
    }

    public function performEdits()
    {
        foreach ($this->actions as $action) {
            $action->performEdit($this->buffer);
        }
    }
}
