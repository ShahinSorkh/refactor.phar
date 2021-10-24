<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;

class FileTest extends TestCase
{
    private function createFileSystem()
    {
        return vfsStream::setup(
            'project',
            0644,
            [
                'src' => [
                    'Foo' => [
                        'Bar.php' => '<?php noop() ?>',
                    ],
                ],
            ]
        );
    }

    public function test_get_relative_path_respects_mixed_windows_paths_and_working_directory_trailing_slashs()
    {
        $root = $this->createFileSystem();
        $workingDir = $root->getChild('src')->url().'/';

        $file = File::createFromPath(
            $root->getChild('src')->url().'\Foo\Bar.php',
            $workingDir
        );

        $this->assertEquals("Foo\Bar.php", $file->getRelativePath());
    }

    public function test_relative_path_construction_for_absolute_vfs_files()
    {
        $src = $this->createFileSystem()->getChild('src')->url();
        $bar = $src.DIRECTORY_SEPARATOR.'Foo'.DIRECTORY_SEPARATOR.'Bar.php';

        $file = File::createFromPath($bar, $notRelatedWorkingDir = __DIR__);
        $this->assertEquals('vfs://project/src/Foo/Bar.php', $file->getRelativePath());
    }

    public static function dataExtractPsr0ClassName()
    {
        return [
            [new PhpName('Foo', 'Foo'), 'src'.DIRECTORY_SEPARATOR.'Foo.php'],
            [new PhpName('Foo\Bar', 'Bar'), 'src'.DIRECTORY_SEPARATOR.'Foo'.DIRECTORY_SEPARATOR.'Bar.php'],
        ];
    }

    /**
     * @dataProvider dataExtractPsr0ClassName
     *
     * @param mixed $expectedClassName
     * @param mixed $fileName
     */
    public function test_extract_psr0_class_name($expectedClassName, $fileName)
    {
        $file = new File($fileName, '<?php');
        $actualClassName = $file->extractPsr0ClassName();

        $this->assertTrue($expectedClassName->equals($actualClassName), "- $expectedClassName\n+ $actualClassName");
    }
}
