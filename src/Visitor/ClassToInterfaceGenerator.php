<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Visitor;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Generate a new Interface wth the methods of a concrete class
 */
class ClassToInterfaceGenerator extends NodeVisitorAbstract {

    protected $className;
    protected $interfaceName;

    public function __construct(string $className, string $interfaceName) {
        $this->className = $className;
        $this->interfaceName = $interfaceName;
    }

    public function enterNode(Node $node) {

        // filtering class in this namespace :
        if ($node instanceof Namespace_) {
            $node->stmts = array_filter($node->stmts, function($node) {
                return ($node instanceof Class_) && ((string) $node->name === $this->className);
            });

            if (0 === count($node->stmts)) {
                throw new LogicException("'{$this->className}' class not found");
            }

            return $node;
        }

        // transforms the class into an interface :
        if ($node instanceof Class_) {
            // Keep only methods and constants :
            $node->stmts = array_filter($node->stmts, function($node) {
                if (($node instanceof ClassConst) && $node->isPublic()) {
                    return true;
                }

                if (($node instanceof ClassMethod) && ((string) $node->name !== '__construct') && ($node->isPublic())) {
                    return true;
                }

                return false;
            });

            // creating the interface Node :
            $newInterface = new Interface_($this->interfaceName,
                    ['stmts' => $node->stmts],
                    ['comments' => $node->getComments()]);

            return $newInterface;
        }

        // removing implementation in methods :
        if ($node instanceof ClassMethod) {
            $node->stmts = null;
        }
    }

}
