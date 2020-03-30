<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

/**
 * This visitor adds implements from an Interface for Factory Method DP
 * Remove public const since they're inherited from the interface
 * Rename the class name
 */
class ClassInheritsFromPublicInterface extends NodeVisitorAbstract
{

    protected $className;
    protected $interfaceName;
    protected $newClassName;

    /**
     * Ctor
     * @param string $className the original concrete class name to refactor
     * @param string $interfaceName The interface for the Model
     * @param string $newClassName The new class name for the Model
     */
    public function __construct(string $className, string $interfaceName, string $newClassName)
    {
        $this->className = $className;
        $this->interfaceName = $interfaceName;
        $this->newClassName = $newClassName;
    }

    public function enterNode(Node $node)
    {
        // rename a class, implements the interface and remove public class constants :
        if (($node instanceof Class_) && ((string) $node->name === $this->className)) {
            $node->name = new Node\Identifier($this->newClassName);
            $node->implements[] = new Node\Identifier($this->interfaceName);
            $node->stmts = array_filter($node->stmts, function(Node $node) {
                if (($node instanceof Node\Stmt\ClassConst) && ($node->isPublic())) {
                    return false;
                }

                return true;
            });

            return $node;
        }
    }

}
