<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Defined variables that are used or assigned.
 */
class DefinedVariables
{
    /**
     * Name of variables that are "used" locally.
     *
     * @var array
     */
    protected $readAccess;

    /**
     * Name of variables that are assigned.
     *
     * @var array
     */
    protected $changeAccess;

    public function __construct(array $readAccess = [], array $changeAccess = [])
    {
        $this->readAccess = $readAccess;
        $this->changeAccess = $changeAccess;
    }

    public function read()
    {
        return array_keys($this->readAccess);
    }

    public function changed()
    {
        return array_keys($this->changeAccess);
    }

    public function all()
    {
        $all = $this->readAccess;

        foreach ($this->changeAccess as $name => $lines) {
            if (!isset($all[$name])) {
                $all[$name] = [];
            }

            $all[$name] = array_unique(array_merge($all[$name], $lines));

            sort($all[$name]);
        }

        return $all;
    }

    /**
     * Does list contain the given variable?
     *
     * @return bool
     */
    public function contains(Variable $variable)
    {
        return
            isset($this->readAccess[$variable->getName()])
            || isset($this->changeAccess[$variable->getName()])
        ;
    }

    public function variablesFromSelectionUsedAfter(DefinedVariables $selection)
    {
        return $this->filterVariablesFromSelection(
            $selection->changed(),
            $selection,
            function ($lastUsedLine, $endLine) {
                return $lastUsedLine > $endLine;
            },
            'max'
        );
    }

    public function variablesFromSelectionUsedBefore(DefinedVariables $selection)
    {
        return $this->filterVariablesFromSelection(
            $selection->read(),
            $selection,
            function ($lastUsedLine, $endLine) {
                return $lastUsedLine < $endLine;
            },
            'min'
        );
    }

    private function filterVariablesFromSelection($selectedVariables, DefinedVariables $selection, \Closure $filter, $reducer)
    {
        $variablesUsed = [];

        $compareLine = $reducer == 'max'
            ? $selection->endLine()
            : $selection->startLine();
        $knownVariables = $this->all();

        foreach ($selectedVariables as $variable) {
            if (!isset($knownVariables[$variable])) {
                continue;
            }

            $lastUsedLine = $reducer($knownVariables[$variable]);

            if ($filter($lastUsedLine, $compareLine)) {
                $variablesUsed[] = $variable;
            }
        }

        return $variablesUsed;
    }

    private function endLine()
    {
        if (!$this->readAccess && !$this->changeAccess) {
            return 0;
        }

        return max(array_merge(array_map('max', $this->readAccess), array_map('max', $this->changeAccess)));
    }

    private function startLine()
    {
        if (!$this->readAccess && !$this->changeAccess) {
            return 0;
        }

        return min(array_merge(array_map('min', $this->readAccess), array_map('min', $this->changeAccess)));
    }
}
