<?php

namespace QafooLabs\Refactoring\Domain\Model;

use QafooLabs\Collections\Hashable;

class PhpNameChange implements Hashable
{
    private $fromName;

    private $toName;

    public function __construct(PhpName $fromName, PhpName $toName)
    {
        $this->fromName = $fromName;
        $this->toName = $toName;
    }

    public function affects(PhpName $name)
    {
        return $name->isAffectedByChangesTo($this->fromName);
    }

    public function change(PhpName $name)
    {
        return $name->change($this->fromName, $this->toName);
    }

    public function hashCode()
    {
        return '1373136290'.$this->fromName->hashCode().$this->toName->hashCode();
    }
}
