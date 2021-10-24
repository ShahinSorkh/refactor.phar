<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\Directory;

class DirectoryTest extends TestCase
{
    public function test_find_all_php_files_recursivly()
    {
        $directory = new Directory(__DIR__, __DIR__);
        $files = $directory->findAllPhpFilesRecursivly();

        $this->assertContainsOnly('QafooLabs\Refactoring\Domain\Model\File', $files);
    }

    public function test_removes_duplicates()
    {
        vfsStreamWrapper::register();

        $structure = [
            'src' => [
                'src' => [],
                'Foo' => [
                    'src' => [],
                    'Foo' => [],
                    'Bar.php' => '<?php',
                ],
            ],
        ];

        vfsStream::create($structure, vfsStream::setup('project'));
        $dir = vfsStream::url('project/src');

        $directory = new Directory($dir, $dir);
        $files = $directory->findAllPhpFilesRecursivly();

        $foundFiles = [];
        foreach ($files as $f => $file) {
            $foundFiles[] = $f;
        }

        $this->assertEquals(['vfs://project'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Foo'.DIRECTORY_SEPARATOR.'Bar.php'], $foundFiles);
    }
}
