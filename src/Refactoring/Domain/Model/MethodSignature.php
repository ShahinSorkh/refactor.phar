<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Representation of a method signature and all its parts (name, visibility, arguments, returnVariables).
 */
class MethodSignature
{
    public const IS_PUBLIC = 1;

    public const IS_PRIVATE = 2;

    public const IS_PROTECTED = 4;

    public const IS_STATIC = 8;

    public const IS_FINAL = 16;

    private $name;

    private $flags;

    private $arguments;

    private $returnVariables;

    public function __construct($name, $flags = self::IS_PRIVATE, array $arguments = [], $returnVariables = [])
    {
        $this->name = $name;
        $this->flags = $this->change($flags);
        $this->arguments = $arguments;
        $this->returnVariables = $returnVariables;
    }

    private function change($flags)
    {
        $visibility = (self::IS_PRIVATE | self::IS_PROTECTED | self::IS_PUBLIC);
        $allowedVisibilities = [self::IS_PRIVATE, self::IS_PROTECTED, self::IS_PUBLIC];

        if (($flags & $visibility) === 0) {
            $flags = $flags | self::IS_PRIVATE;
        }

        if (!in_array(($flags & $visibility), $allowedVisibilities)) {
            throw new \InvalidArgumentException('Mix of visibilities is not allowed.');
        }

        return $flags;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Is this method private?
     *
     * @return bool
     */
    public function isPrivate()
    {
        return ($this->flags & self::IS_PRIVATE) > 0;
    }

    /**
     * Is this method static?
     *
     * @return bool
     */
    public function isStatic()
    {
        return ($this->flags & self::IS_STATIC) > 0;
    }

    /**
     * @return array
     */
    public function returnVariables()
    {
        return $this->returnVariables;
    }

    /**
     * @return array
     */
    public function arguments()
    {
        return $this->arguments;
    }
}
