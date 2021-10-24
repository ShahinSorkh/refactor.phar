<?php

namespace Tests\QafooLabs\Refactoring\Utils;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Utils\TransformIterator;

class TransformIteratorTest extends TestCase
{
    public function test_transform_values()
    {
        $strings = new ReverseStringTransformIterator(new \ArrayIterator(['Hello', 'World']));

        $this->assertEquals(['olleH', 'dlroW'], iterator_to_array($strings));
    }
}

class ReverseStringTransformIterator extends TransformIterator
{
    protected function transform($value)
    {
        return strrev($value);
    }
}
