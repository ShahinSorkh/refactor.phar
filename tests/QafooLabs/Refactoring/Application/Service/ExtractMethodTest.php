<?php

namespace Tests\QafooLabs\Refactoring\Application\Service;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Application\ExtractMethod;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;

class ExtractMethodTest extends TestCase
{
    private $applyCommand;

    private $refactoring;

    public function setUp(): void
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\PatchBuilder\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($this->applyCommand);

        $this->refactoring = new ExtractMethod($scanner, $codeAnalysis, $editor);
    }

    /**
     * @group integration
     */
    public function test_refactor_simple_method()
    {
        $patch = $this->refactoring->refactor(new File(
            'foo.php',
            '<?php'.PHP_EOL
            .'class Foo'.PHP_EOL
            .'{'.PHP_EOL
            .'    public function main()'.PHP_EOL
            .'    {'.PHP_EOL
            .'        echo "Hello World";'.PHP_EOL
            .'    }'.PHP_EOL
            .'}'.PHP_EOL
        ), LineRange::fromString('6-6'), 'helloWorld');

        \Phake::verify($this->applyCommand)->apply(
            '--- a/foo.php'.PHP_EOL
            .'+++ b/foo.php'.PHP_EOL
            .'@@ -3,6 +3,11 @@'.PHP_EOL
            .' {'.PHP_EOL
            .'     public function main()'.PHP_EOL
            .'     {'.PHP_EOL
            .'+        $this->helloWorld();'.PHP_EOL
            .'+    }'.PHP_EOL
            .'+'.PHP_EOL
            .'+    private function helloWorld()'.PHP_EOL
            .'+    {'.PHP_EOL
            .'         echo "Hello World";'.PHP_EOL
            .'     }'.PHP_EOL
            .' }'.PHP_EOL
        );
    }

    /**
     * @group regression
     * @group GH-4
     */
    public function test_variable_used_before_and_after_extracted_slice()
    {
        $this->markTestIncomplete('Failing over some invisible whitespace issue?');

        $patch = $this->refactoring->refactor(new File(
            'foo.php',
            '<?php'.PHP_EOL
            .'lass Foo'.PHP_EOL
            .'{'.PHP_EOL
            .'    public function main()'.PHP_EOL
            .'    {'.PHP_EOL
            .'        $foo = "bar";'.PHP_EOL
            .'        $baz = array();'.PHP_EOL
            .''.PHP_EOL
            .'        $foo = strtolower($foo);'.PHP_EOL
            .'        $baz[] = $foo;'.PHP_EOL
            .''.PHP_EOL
            .'        return new Something($foo, $baz);'.PHP_EOL
            .'    }'.PHP_EOL
            .'}'.PHP_EOL
        ), LineRange::fromString('9-10'), 'extract');

        \Phake::verify($this->applyCommand)->apply(
            '--- a/foo.php'.PHP_EOL
            .'+++ b/foo.php'.PHP_EOL
            .'@@ -6,9 +6,16 @@'.PHP_EOL
            .'         $foo = "bar";'.PHP_EOL
            .'         $baz = array();'.PHP_EOL
            .''.PHP_EOL
            .'+        list($foo, $baz) = $this->extract($foo, $baz);'.PHP_EOL
            .'+'.PHP_EOL
            .'+        return new Something($foo, $baz);'.PHP_EOL
            .'+    }'.PHP_EOL
            .'+'.PHP_EOL
            .'+    private function extract($foo, $baz)'.PHP_EOL
            .'+    {'.PHP_EOL
            .'         $foo = strtolower($foo);'.PHP_EOL
            .'         $baz[] = $foo;'.PHP_EOL
            .''.PHP_EOL
            .'-        return new Something($foo, $baz);'.PHP_EOL
            .'+        return array($foo, $baz);'.PHP_EOL
            .'     }'.PHP_EOL
            .' }'.PHP_EOL
        );
    }
}
