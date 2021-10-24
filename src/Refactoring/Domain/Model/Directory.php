<?php

namespace QafooLabs\Refactoring\Domain\Model;

use CallbackFilterIterator as StandardCallbackFilterIterator;
use QafooLabs\Refactoring\Utils\CallbackFilterIterator;
use QafooLabs\Refactoring\Utils\CallbackTransformIterator;

/**
 * A directory in a project.
 */
class Directory
{
    /** @var array */
    private $paths;

    /** @var string */
    private $workingDirectory;

    public function __construct($paths, $workingDirectory)
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        $this->paths = $paths;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @return File[]
     */
    public function findAllPhpFilesRecursivly()
    {
        $workingDirectory = $this->workingDirectory;

        $iterator = new \AppendIterator;

        foreach ($this->paths as $path) {
            $iterator->append(
                new CallbackTransformIterator(
                    new CallbackFilterIterator(
                        new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($path),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        ),
                        function (\SplFileInfo $file) {
                            return substr($file->getFilename(), -4) === '.php';
                        }
                    ),
                    function ($file) use ($workingDirectory) {
                        return File::createFromPath($file->getPathname(), $workingDirectory);
                    }
                )
            );
        }

        $files = iterator_to_array($iterator);

        return new StandardCallbackFilterIterator($iterator, function ($file, $filename) use ($files) {
            return !in_array($filename, $files);
        });
    }
}
