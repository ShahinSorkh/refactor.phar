<?php

namespace Tests\QafooLabs\Refactoring\Utils;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Utils\CallbackFilterIterator;

class CallbackFilterIteratorTest extends TestCase
{
    public function test_filter_empty_elements()
    {
        $values = new CallbackFilterIterator(
            new \ArrayIterator([1, null, false, '', 2]),
            function ($value) {
                return !empty($value);
            }
        );

        $this->assertEquals([1, 2], array_values(iterator_to_array($values)));
    }
}
