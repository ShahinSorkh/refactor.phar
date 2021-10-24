<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * A range of lines.
 */
class LineRange
{
    private $lines = [];

    /**
     * @param mixed $line
     *
     * @return LineRange
     */
    public static function fromSingleLine($line)
    {
        $list = new self();
        $list->lines[$line] = $line;

        return $list;
    }

    /**
     * @param mixed $start
     * @param mixed $end
     *
     * @return LineRange
     */
    public static function fromLines($start, $end)
    {
        $list = new self();

        for ($i = $start; $i <= $end; $i++) {
            $list->lines[$i] = $i;
        }

        return $list;
    }

    /**
     * @param mixed $range
     *
     * @return LineRange
     */
    public static function fromString($range)
    {
        [$start, $end] = explode('-', $range);

        return self::fromLines($start, $end);
    }

    public function isInRange($line)
    {
        return isset($this->lines[$line]);
    }

    public function getStart()
    {
        return (int) min($this->lines);
    }

    public function getEnd()
    {
        return (int) max($this->lines);
    }

    public function sliceCode($code)
    {
        $selectedCode = explode("\n", $code);
        $numLines = count($selectedCode);

        for ($i = 0; $i < $numLines; $i++) {
            if (!$this->isInRange($i + 1)) {
                unset($selectedCode[$i]);
            }
        }

        return array_values($selectedCode);
    }
}
