<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\EditingAction\AddProperty;
use QafooLabs\Refactoring\Domain\Model\EditingAction\LocalVariableToInstance;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\Variable;

class ConvertLocalToInstanceVariable extends SingleFileRefactoring
{
    /** @var Variable */
    private $convertVariable;

    /**
     * @param int $line
     */
    public function refactor(File $file, $line, Variable $convertVariable)
    {
        $this->file = $file;
        $this->line = $line;
        $this->convertVariable = $convertVariable;

        $this->assertIsInsideMethod();

        $this->startEditingSession();
        $this->addProperty();
        $this->convertVariablesToInstanceVariables();
        $this->completeEditingSession();
    }

    private function addProperty()
    {
        $line = $this->codeAnalysis->getLineOfLastPropertyDefinedInScope($this->file, $this->line);

        $this->session->addEdit(
            new AddProperty($line, $this->convertVariable->getName())
        );
    }

    private function convertVariablesToInstanceVariables()
    {
        $definedVariables = $this->getDefinedVariables();

        if (!$definedVariables->contains($this->convertVariable)) {
            throw RefactoringException::variableNotInRange($this->convertVariable, $selectedMethodLineRange);
        }

        $this->session->addEdit(new LocalVariableToInstance($definedVariables, $this->convertVariable));
    }
}
