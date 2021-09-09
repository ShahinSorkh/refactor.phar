<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Expr\StaticCall;

/**
 * Visitor for PHP Parser collecting PHP Names from an AST.
 */
class PhpNameCollector extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $nameDeclarations = array();
    /**
     * @var array
     */
    private $useStatements = array();
    /**
     * @var string
     */
    private $currentNamespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                if ($use instanceof UseUse) {
                    $name = implode('\\', $use->name->parts);

                    $this->useStatements[$use->alias ?: $use->name->getLast()] = $name;
                    $this->nameDeclarations[] = array(
                        'alias' => $name,
                        'fqcn' => $name,
                        'line' => $use->getLine(),
                        'type' => 'use',
                    );
                }
            }
        }

        if ($node instanceof New_ && $node->class instanceof Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->class->isFullyQualified()),
                'line' => $node->getLine(),
                'type' => 'usage',
            );
        }

        if ($node instanceof StaticCall && $node->class instanceof Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->class->isFullyQualified()),
                'line' => $node->getLine(),
                'type' => 'usage',
            );
        }

        if ($node instanceof Class_) {
            $className = $node->name->name;

            $this->nameDeclarations[] = array(
                'alias' => $className,
                'fqcn' => $this->fullyQualifiedNameFor($className, false),
                'line' => $node->getLine(),
                'type' => 'class',
            );

            if ($node->extends) {
                $usedAlias = implode('\\', $node->extends->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->extends->isFullyQualified()),
                    'line' => $node->extends->getLine(),
                    'type' => 'usage',
                );
            }

            foreach ($node->implements as $implement) {
                $usedAlias = implode('\\', $implement->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $implement->isFullyQualified()),
                    'line' => $implement->getLine(),
                    'type' => 'usage',
                );
            }
        }

        if ($node instanceof Namespace_) {
            $this->currentNamespace = implode('\\', $node->name->parts);
            $this->useStatements = array();

            $this->nameDeclarations[] = array(
                'alias' => $this->currentNamespace,
                'fqcn' => $this->currentNamespace,
                'line' => $node->name->getLine(),
                'type' => 'namespace',
            );
        }
    }

    private function fullyQualifiedNameFor($alias, $isFullyQualified)
    {
        $isAbsolute = $alias[0] === "\\";

        if ($isAbsolute || $isFullyQualified) {
            $class = $alias;
        } else if (isset($this->useStatements[$alias])) {
            $class = $this->useStatements[$alias];
        } else {
            $class = ltrim($this->currentNamespace . '\\' . $alias, '\\');
        }

        return $class;
    }

    public function collectedNameDeclarations()
    {
        return $this->nameDeclarations;
    }

    public function namespaceOfClass($classDeclaration)
    {
        $classNameParts = explode('\\', $classDeclaration['fqcn']);
        $classNamespace = implode('\\', array_slice($classNameParts, 0, -1));

        foreach ($this->nameDeclarations as $declaration) {
            if ($declaration['type'] !== 'namespace') continue;

            if ($declaration['fqcn'] === $classNamespace) {
                return $declaration;
            }
        }

        return null;
    }
}
