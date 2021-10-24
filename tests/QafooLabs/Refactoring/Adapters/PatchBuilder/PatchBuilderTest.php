<?php

namespace Tests\QafooLabs\Refactoring\Adapters\PatchBuilder;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchBuilder;

class PatchBuilderTest extends TestCase
{
    /** @var PatchBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new PatchBuilder(
            "line1".PHP_EOL
            ."line2".PHP_EOL
            ."line3".PHP_EOL
            ."line4".PHP_EOL
            ."line5".PHP_EOL
            ."line6".PHP_EOL
            ."line7".PHP_EOL
            ."line8".PHP_EOL
            ."line9".PHP_EOL
        );
    }

    public function test_change_token_on_line_alone()
    {
        $this->builder->changeToken(4, 'line4', 'linefour');

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,7 +1,7 @@'.PHP_EOL
                  .' line1'.PHP_EOL
                  .' line2'.PHP_EOL
                  .' line3'.PHP_EOL
                  .'-line4'.PHP_EOL
                  .'+linefour'.PHP_EOL
                  .' line5'.PHP_EOL
                  .' line6'.PHP_EOL
                  .' line7'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_is_case_sensitive()
    {
        $this->builder = new PatchBuilder('$bar = new Bar();');
        $this->builder->changeToken(1, 'Bar', 'Foo');

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,1 +1,1 @@'.PHP_EOL
                  .'-$bar = new Bar();'.PHP_EOL
                  .'+$bar = new Foo();'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_alone_on_indented_line()
    {
        $this->builder = new PatchBuilder(
            "line1".PHP_EOL
            ."    line2".PHP_EOL
            ."line3".PHP_EOL
        );

        $this->builder->changeToken(2, 'line2', 'linetwo');

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,3 +1,3 @@'.PHP_EOL
                  .' line1'.PHP_EOL
                  .'-    line2'.PHP_EOL
                  .'+    linetwo'.PHP_EOL
                  .' line3'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_with_multiple_tokens_one_one_line()
    {
        $this->builder = new PatchBuilder(
            "line1".PHP_EOL
            ."    echo \$var . ' = ' . \$var;".PHP_EOL
            ."line3".PHP_EOL
        );

        $this->builder->changeToken(2, 'var', 'variable');

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,3 +1,3 @@'.PHP_EOL
                  .' line1'.PHP_EOL
                  .'-    echo $var . \' = \' . $var;'.PHP_EOL
                  .'+    echo $variable . \' = \' . $variable;'.PHP_EOL
                  .' line3'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_with_underscore()
    {
        $this->builder = new PatchBuilder(
            "line1".PHP_EOL
            ."    echo \$my_variable;".PHP_EOL
            ."line3".PHP_EOL
        );

        $this->builder->changeToken(2, 'my_variable', 'myVariable');

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,3 +1,3 @@'.PHP_EOL
                  .' line1'.PHP_EOL
                  .'-    echo $my_variable;'.PHP_EOL
                  .'+    echo $myVariable;'.PHP_EOL
                  .' line3'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_append_to_line()
    {
        $this->builder->appendToLine(5, ['line5.1', 'line5.2']);

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -3,6 +3,8 @@'.PHP_EOL
                  .' line3'.PHP_EOL
                  .' line4'.PHP_EOL
                  .' line5'.PHP_EOL
                  .'+line5.1'.PHP_EOL
                  .'+line5.2'.PHP_EOL
                  .' line6'.PHP_EOL
                  .' line7'.PHP_EOL
                  .' line8'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_lines()
    {
        $this->builder->changeLines(5, ['linefive', 'linefive.five']);

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -2,7 +2,8 @@'.PHP_EOL
                  .' line2'.PHP_EOL
                  .' line3'.PHP_EOL
                  .' line4'.PHP_EOL
                  .'-line5'.PHP_EOL
                  .'+linefive'.PHP_EOL
                  .'+linefive.five'.PHP_EOL
                  .' line6'.PHP_EOL
                  .' line7'.PHP_EOL
                  .' line8'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_remove_line()
    {
        $this->builder->removeLine(5);

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -2,7 +2,6 @@'.PHP_EOL
                  .' line2'.PHP_EOL
                  .' line3'.PHP_EOL
                  .' line4'.PHP_EOL
                  .'-line5'.PHP_EOL
                  .' line6'.PHP_EOL
                  .' line7'.PHP_EOL
                  .' line8'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_replace_lines()
    {
        $this->builder->replaceLines(4, 6, ['hello', 'world']);

        $expected =
                  '--- a/'.PHP_EOL
                  .'+++ b/'.PHP_EOL
                  .'@@ -1,9 +1,8 @@'.PHP_EOL
                  .' line1'.PHP_EOL
                  .' line2'.PHP_EOL
                  .' line3'.PHP_EOL
                  .'-line4'.PHP_EOL
                  .'-line5'.PHP_EOL
                  .'-line6'.PHP_EOL
                  .'+hello'.PHP_EOL
                  .'+world'.PHP_EOL
                  .' line7'.PHP_EOL
                  .' line8'.PHP_EOL
                  .' line9'.PHP_EOL;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_get_original_lines()
    {
        $this->assertEquals(
            ['line4', 'line5', 'line6'],
            $this->builder->getOriginalLines(4, 6)
        );
    }
}
