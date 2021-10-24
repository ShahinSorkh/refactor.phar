<?php

namespace Tests\QafooLabs\Refactoring\Adapters\PHPParser;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;

class ParserPhpNameScannerTest extends TestCase
{
    public function test_find_names()
    {
        $file = File::createFromPath(__FILE__, __DIR__);
        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            [
                new PhpNameOccurance(new PhpName('Tests\QafooLabs\Refactoring\Adapters\PHPParser', 'Tests\QafooLabs\Refactoring\Adapters\PHPParser', PhpName::TYPE_NAMESPACE), $file, 3),
                new PhpNameOccurance(new PhpName('PHPUnit\Framework\TestCase', 'PHPUnit\Framework\TestCase', PhpName::TYPE_USE), $file, 5),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', PhpName::TYPE_USE), $file, 6),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'QafooLabs\Refactoring\Domain\Model\File', PhpName::TYPE_USE), $file, 7),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpName', 'QafooLabs\Refactoring\Domain\Model\PhpName', PhpName::TYPE_USE), $file, 8),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpNameOccurance', 'QafooLabs\Refactoring\Domain\Model\PhpNameOccurance', PhpName::TYPE_USE), $file, 9),
                new PhpNameOccurance(new PhpName('Tests\QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScannerTest', 'ParserPhpNameScannerTest', PhpName::TYPE_CLASS), $file, 11),
                new PhpNameOccurance(new PhpName('PHPUnit\Framework\TestCase', 'TestCase', PhpName::TYPE_USAGE), $file, 11),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File', PhpName::TYPE_USAGE), $file, 15),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner', PhpName::TYPE_USAGE), $file, 16),
            ],
            array_slice($names, 0, 10)
        );
    }

    public function test_regression_find_names_detects_fqcn_correctly()
    {
        $file = new File(
            'Fqcn.php',
            <<<'PHP'
<?php

namespace Bar;

class Fqcn
{
    public function main()
    {
        new \Bar\Qux\Adapter($flag);
    }
}
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = array_values(array_filter(
            $scanner->findNames($file),
            function ($occurance) {
                return $occurance->name()->type() === PhpName::TYPE_USAGE;
            }
        ));

        $this->assertEquals(
            [
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter'),
                    $file,
                    9
                ),
            ],
            $names
        );
    }

    public function test_find_names_finds_parent_for_php_name_in_single_line_use_statement()
    {
        $file = new File(
            'Fqcn.php',
            <<<'PHP'
<?php

use Bar\Qux\Adapter;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            [
                new PhpNameOccurance(
                    new PhpName(
                        'Bar\Qux\Adapter',
                        'Bar\Qux\Adapter',
                        PhpName::TYPE_USE
                    ),
                    $file,
                    3
                ),
            ],
            $names
        );
    }

    public function test_find_names_finds_parent_for_php_name_in_multi_line_use_statement()
    {
        $file = new File(
            'Fqcn.php',
            <<<'PHP'
<?php

use Bar\Qux\Adapter,
    Bar\Qux\Foo;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            [
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter', PhpName::TYPE_USE),
                    $file,
                    3
                ),
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Foo', 'Bar\Qux\Foo', PhpName::TYPE_USE),
                    $file,
                    4
                ),
            ],
            $names
        );
    }
}
