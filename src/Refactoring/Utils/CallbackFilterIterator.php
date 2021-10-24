<?php

namespace QafooLabs\Refactoring\Utils;

use FilterIterator;

/**
 * FilterIterator using callbacks to implement the accept routine.
 */
class CallbackFilterIterator extends \FilterIterator
{
    /** @var callable */
    private $filter;

    public function __construct($iterator, $filter)
    {
        parent::__construct($iterator);
        $this->filter = $filter;
    }

    public function accept()
    {
        $filter = $this->filter;

        return $filter($this->getInnerIterator()->current());
    }
}
