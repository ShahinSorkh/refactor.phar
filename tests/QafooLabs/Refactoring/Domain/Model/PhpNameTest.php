<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\PhpName;

class PhpNameTest extends TestCase
{
    public function test_is_affected_by_changes_to_itself()
    {
        $name = new PhpName("Foo\Bar\Baz", 'Baz');

        $this->assertTrue($name->isAffectedByChangesTo($name));
    }

    public function test_is_not_affected_by_changes_to_non_relative_part()
    {
        $name = new PhpName("Foo\Bar\Baz", 'Baz');
        $changing = new PhpName("Foo\Bar", "Foo\Bar");

        $this->assertFalse($name->isAffectedByChangesTo($changing));
    }

    public function test_is_affected_by_relative_changes()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz");
        $changing = new PhpName("Foo\Bar", "Foo\Bar");

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function test_relative_changes()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz");
        $from = new PhpName("Foo\Bar", "Foo\Bar");
        $to = new PhpName("Foo\Baz", "Foo\Baz");

        $newName = $name->change($from, $to);

        $this->assertEquals('Foo\Baz\Baz', $newName->fullyQualifiedName());
        $this->assertEquals('Baz\Baz', $newName->relativeName());
    }

    public function test_regression()
    {
        $name = new PhpName("Bar\Bar", "Bar\Bar");
        $changing = new PhpName('Bar', 'Bar');

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function test_regression2()
    {
        $name = new PhpName('Foo\\Foo', 'Foo\\Foo');
        $from = new PhpName('Foo\\Foo', 'Foo');
        $to = new PhpName('Foo\\Bar', 'Bar');

        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\\Bar', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\\Bar', $changed->relativeName());
    }

    public function test_regression3()
    {
        $name = new PhpName('Foo\\Foo', 'Foo\\Foo');
        $from = new PhpName('Foo\\Foo', 'Foo\\Foo');
        $to = new PhpName('Foo\\Bar\\Foo', 'Foo\\Bar\\Foo');

        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\\Bar\\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\\Bar\\Foo', $changed->relativeName());
    }

    public function test_create_declaration_name()
    {
        $name = PhpName::createDeclarationName('Foo\Bar\Baz');

        $this->assertEquals('Foo\Bar\Baz', $name->fullyQualifiedName());
        $this->assertEquals('Baz', $name->relativeName());
        $this->assertEquals(PhpName::TYPE_CLASS, $name->type());
    }

    public function test_regression4()
    {
        $name = new PhpName('Foo', 'Foo');
        $from = new PhpName('Foo\\Foo', 'Foo');
        $to = new PhpName('Foo\\Bar', 'Bar');

        $this->assertFalse($name->isAffectedByChangesTo($from), 'Namespace should not be affected by changes to Class in namespace.');
    }

    public function test_regression5()
    {
        $from = new PhpName("Qafoo\ChangeTrack\ChangeFeed", "Qafoo\ChangeTrack\ChangeFeed");
        $to = new PhpName("Qafoo\ChangeTrack\Analyzer\ChangeFeed", "Qafoo\ChangeTrack\Analyzer\ChangeFeed");
        $name = new PhpName("Qafoo\ChangeTrack\ChangeFeed", 'ChangeFeed');

        $changed = $name->change($from, $to);

        $this->assertEquals('Qafoo\ChangeTrack\Analyzer\ChangeFeed', $changed->fullyQualifiedName());
        $this->assertEQuals('Analyzer\ChangeFeed', $changed->relativeName());
    }

    public function test_regression6()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo');
        $to = new PhpName('Foo\Bar\Baz\Boing', 'Boing');

        $name = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\Bar\Baz\Boing', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\Bar\Baz\Boing', $changed->relativeName());
    }

    public function test_regression7()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo');
        $to = new PhpName('Foo\Boing', 'Boing');

        $name = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\Boing', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\Boing', $changed->relativeName());
    }

    public function test_regression8()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $to = new PhpName('Foo\Boing', 'Foo\Boing');

        $name = new PhpName('Foo\Foo', 'Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo', $changed->relativeName());
    }

    public function test_change_keeps_type()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $to = new PhpName('Foo\Boing', 'Foo\Boing');

        $name = new PhpName('Foo\Foo', 'Foo\Foo', PhpName::TYPE_NAMESPACE);
        $changed = $name->change($from, $to);

        $this->assertEquals(PhpName::TYPE_NAMESPACE, $changed->type());
    }

    public function test_add_relative_name_when_namespace_expands()
    {
        $from = new PhpName('Foo', 'Foo');
        $to = new PhpName('Foo\Bar', 'Foo\Bar');

        $name = new PhpName('Foo\Foo', 'Foo');
        $changed = $name->change($from, $to);

        $this->assertFalse($name->isAffectedByChangesTo($from));
        $this->assertEquals('Foo\Bar\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Bar\Foo', $changed->relativeName());
    }

    public function test_not_expand_when_relative_name_is_type_class()
    {
        $from = new PhpName('Foo', 'Foo');
        $to = new PhpName('Foo\Bar', 'Foo\Bar');

        $name = new PhpName('Foo\Foo', 'Foo', PhpName::TYPE_CLASS);
        $changed = $name->change($from, $to);

        $this->assertFalse($name->isAffectedByChangesTo($from));
        $this->assertEquals('Foo\Bar\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo', $changed->relativeName());
    }

    /**
     * @dataProvider provideIsFullyQualified
     *
     * @param mixed $fqcn
     * @param mixed $relativeName
     * @param mixed $expected
     */
    public function test_is_fully_qualified($fqcn, $relativeName, $expected = true)
    {
        $name = new PHPName($fqcn, $relativeName);

        $this->assertEquals($expected, $name->isFullyQualified());
    }

    public static function provideIsFullyQualified()
    {
        $tests = [];

        $tests[] = ['Foo', 'Foo', true];
        $tests[] = ['Foo\\Bar\\Foo', 'Foo\\Bar\\Foo', true];

        $tests[] = ['Foo\\Bar\\Foo', 'Foo', false];
        $tests[] = ['Foo\\Bar\\Foo', 'Bar\\Foo', false];

        return $tests;
    }

    public function test_get_short_name_returns_last_part_for_fqcn()
    {
        $name = new PhpName('Foo\\Bar', 'Foo\\Bar', null, null);
        $short = new PhpName('Foo', 'Foo', null, null);

        $this->assertEquals('Bar', $name->shortName());
        $this->assertEquals('Foo', $short->shortName());
    }

    public function test_is_use_statement_when_parent_is_a_use_statement()
    {
        $name = new PhpName('Foo\\Bar', 'Foo\\Bar', PhpName::TYPE_USE);

        $this->assertTrue($name->isUse());
    }
}
