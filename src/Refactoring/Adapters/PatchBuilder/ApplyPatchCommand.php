<?php

namespace QafooLabs\Refactoring\Adapters\PatchBuilder;

interface ApplyPatchCommand
{
    /**
     * @var string
     *
     * @param mixed $patch
     */
    public function apply($patch);
}
