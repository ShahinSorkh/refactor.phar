<?php

namespace Tests\QafooLabs\Refactoring\Utils;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Utils\ToStringIterator;

class ToStringIteratorTest extends TestCase
{
    public function test_converts_objects_to_strings()
    {
        $data = [
            new StringableClass('value1'),
            new StringableClass('value2'),
        ];

        $it = new ToStringIterator(new \ArrayIterator($data));

        $this->assertEquals(
            ['value1', 'value2'],
            iterator_to_array($it)
        );
    }
}

class StringableClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
