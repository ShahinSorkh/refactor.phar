<?php

namespace QafooLabs\Refactoring\Utils;

class CallbackTransformIterator extends TransformIterator
{
    private $transformer;

    public function __construct($iterator, $transformer)
    {
        parent::__construct($iterator);
        $this->transformer = $transformer;
    }

    protected function transform($value)
    {
        $transformer = $this->transformer;

        return $transformer($value);
    }
}
