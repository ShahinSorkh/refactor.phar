<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\PhpNames;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;
use QafooLabs\Refactoring\Domain\Model\PhpNames\NoImportedUsagesFilter;

class NoImportedUsagesFilterTest extends TestCase
{
    /**
     * @group GH-31
     * @test
     */
    public function it_filters_imported_php_name_usages()
    {
        $file = new File('foo.php', 'code');
        $filter = new NoImportedUsagesFilter();
        $names = $filter->filter([
            new PhpNameOccurance(new PhpName('Foo\Bar', 'Foo\Bar', PhpName::TYPE_USE), $file, 12),
            new PhpNameOccurance(new PhpName('Foo\Bar', 'Bar', PhpName::TYPE_USAGE), $file, 12),
        ]);

        $this->assertEquals(1, count($names));
    }
}
