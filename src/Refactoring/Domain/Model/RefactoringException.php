<?php

namespace QafooLabs\Refactoring\Domain\Model;

class RefactoringException extends \Exception
{
    public static function illegalVariableName($name)
    {
        return new self(sprintf('The given variable name "%s" is not valid in PHP.', $name));
    }

    public static function variableNotInRange(Variable $variable, LineRange $range)
    {
        return new self(sprintf(
            'Could not find variable "%s" in line range %d-%d.',
            $variable->getToken(),
            $range->getStart(),
            $range->getEnd()
        ));
    }

    public static function variableNotLocal(Variable $variable)
    {
        return new self(sprintf(
            'Given variable "%s" is required to be local to the current method.',
            $variable->getToken()
        ));
    }

    public static function rangeIsNotInsideMethod(LineRange $range)
    {
        return new self(sprintf(
            'The range %d-%d is not inside one single method.',
            $range->getStart(),
            $range->getEnd()
        ));
    }
}
