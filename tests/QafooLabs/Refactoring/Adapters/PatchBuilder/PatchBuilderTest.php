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
            "line1\n"
            ."line2\n"
            ."line3\n"
            ."line4\n"
            ."line5\n"
            ."line6\n"
            ."line7\n"
            ."line8\n"
            ."line9\n"
        );
    }

    public function test_change_token_on_line_alone()
    {
        $this->builder->changeToken(4, 'line4', 'linefour');

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,7 +1,7 @@
 line1
 line2
 line3
-line4
+linefour
 line5
 line6
 line7

DIFF;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_is_case_sensitive()
    {
        $this->builder = new PatchBuilder('$bar = new Bar();');
        $this->builder->changeToken(1, 'Bar', 'Foo');

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,1 +1,1 @@
-$bar = new Bar();
+$bar = new Foo();

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_alone_on_indented_line()
    {
        $this->builder = new PatchBuilder(
            "line1\n"
            ."    line2\n"
            ."line3\n"
        );

        $this->builder->changeToken(2, 'line2', 'linetwo');

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    line2
+    linetwo
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_with_multiple_tokens_one_one_line()
    {
        $this->builder = new PatchBuilder(
            "line1\n"
            ."    echo \$var . ' = ' . \$var;\n"
            ."line3\n"
        );

        $this->builder->changeToken(2, 'var', 'variable');

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    echo $var . ' = ' . $var;
+    echo $variable . ' = ' . $variable;
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_token_with_underscore()
    {
        $this->builder = new PatchBuilder(
            "line1\n"
            ."    echo \$my_variable;\n"
            ."line3\n"
        );

        $this->builder->changeToken(2, 'my_variable', 'myVariable');

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    echo $my_variable;
+    echo $myVariable;
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_append_to_line()
    {
        $this->builder->appendToLine(5, ['line5.1', 'line5.2']);

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -3,6 +3,8 @@
 line3
 line4
 line5
+line5.1
+line5.2
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_change_lines()
    {
        $this->builder->changeLines(5, ['linefive', 'linefive.five']);

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -2,7 +2,8 @@
 line2
 line3
 line4
-line5
+linefive
+linefive.five
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_remove_line()
    {
        $this->builder->removeLine(5);

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -2,7 +2,6 @@
 line2
 line3
 line4
-line5
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function test_replace_lines()
    {
        $this->builder->replaceLines(4, 6, ['hello', 'world']);

        $expected = <<<'DIFF'
--- a/
+++ b/
@@ -1,9 +1,8 @@
 line1
 line2
 line3
-line4
-line5
-line6
+hello
+world
 line7
 line8
 line9

DIFF;
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
