<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * CodeAnalysis provider.
 */
abstract class CodeAnalysis
{
    /**
     * Is the method in the given line range static?
     *
     * @return bool
     */
    abstract public function isMethodStatic(File $file, LineRange $range);

    /**
     * Get the method start line.
     *
     * @return int
     */
    abstract public function getMethodStartLine(File $file, LineRange $range);

    /**
     * Get the method end line.
     *
     * @return int
     */
    abstract public function getMethodEndLine(File $file, LineRange $range);

    /**
     * @param int $line
     */
    abstract public function getLineOfLastPropertyDefinedInScope(File $file, $line);

    /**
     * Check if the line range is inside exactly one class method.
     *
     * @return bool
     */
    abstract public function isInsideMethod(File $file, LineRange $range);

    /**
     * Find all classes in the file.
     *
     * @return PhpClass[]
     */
    abstract public function findClasses(File $file);

    /**
     * From a range within a method, find the start and end range of that method.
     *
     * @return LineRange
     */
    public function findMethodRange(File $file, LineRange $range)
    {
        $methodStartLine = $this->getMethodStartLine($file, $range);
        $methodEndLine = $this->getMethodEndLine($file, $range);

        return LineRange::fromLines($methodStartLine, $methodEndLine);
    }
}
