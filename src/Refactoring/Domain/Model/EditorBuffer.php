<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Buffer of the Editor that is currently connected to the RefactoringBrowser.
 */
interface EditorBuffer
{
    /**
     * Return the given range of lines from the buffer.
     *
     * @return string[]
     */
    public function getLines(LineRange $range);

    /**
     * Replace LineRange with new lines.
     */
    public function replace(LineRange $range, array $newLines);

    /**
     * Append new lines to a given line.
     *
     * @param int $line
     */
    public function append($line, array $newLines);

    /**
     * Replace a token in in a line with another token.
     *
     * @param int    $line
     * @param string $oldToken
     * @param string $newToken
     */
    public function replaceString($line, $oldToken, $newToken);
}
