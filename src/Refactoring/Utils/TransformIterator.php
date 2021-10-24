<?php

namespace QafooLabs\Refactoring\Utils;

abstract class TransformIterator implements \Iterator
{
    /** @var \Traversable */
    private $iterator;

    public function __construct(\Traversable $iterator)
    {
        $this->iterator = $iterator;
    }

    abstract protected function transform($value);

    public function next()
    {
        return $this->iterator->next();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function current()
    {
        return $this->transform($this->iterator->current());
    }

    public function rewind()
    {
        return $this->iterator->rewind();
    }

    public function key()
    {
        return $this->iterator->key();
    }
}
