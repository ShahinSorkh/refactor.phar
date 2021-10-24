<?php

namespace Tests\QafooLabs\Collections;

use PHPUnit\Framework\TestCase;
use QafooLabs\Collections\Hashable;
use QafooLabs\Collections\Set;

class SetTest extends TestCase
{
    /**
     * @test
     */
    public function when_adding_item_multiple_times__then_only_add_it_once()
    {
        $item = 'A';

        $set = new Set();
        $set->add($item);
        $set->add($item);

        $this->assertEquals(1, count($set));
    }

    /**
     * @test
     */
    public function when_adding_multiple_items__then_count_them_uniquely()
    {
        $item1 = 'A';
        $item2 = 'B';

        $set = new Set();
        $set->add($item1);
        $set->add($item1);
        $set->add($item2);

        $this->assertEquals(2, count($set));
    }

    /**
     * @test
     */
    public function when_adding_hashable_object_multiple_times__then_only_add_it_once()
    {
        $item1 = new FooObject(1);
        $item2 = new FooObject(2);

        $set = new Set();
        $set->add($item1);
        $set->add($item1);
        $set->add($item2);
        $set->add($item2);

        $this->assertEquals(2, count($set));
    }

    /**
     * @test
     */
    public function when_iterating_over_set__then_return_all_unique_items()
    {
        $item1 = 'A';
        $item2 = 'B';

        $set = new Set();
        $set->add($item1);
        $set->add($item2);

        $values = [];

        foreach ($set as $key => $value) {
            $values[$key] = $value;
        }

        $this->assertEquals([0 => 'A', 1 => 'B'], $values);
    }
}

class FooObject implements Hashable
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function hashCode()
    {
        return md5($this->value);
    }
}
