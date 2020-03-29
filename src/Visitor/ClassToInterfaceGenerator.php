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
use PhpParser\NodeVisitor\NameResolver;

/**
 * Generate a new Interface wth the methods of a concrete class
 */
class ClassToInterfaceGenerator extends NameResolver {

    protected $className;
    protected $interfaceName;

    public function __construct(string $className, string $interfaceName) {
        parent::__construct();
        $this->className = $className;
        $this->interfaceName = $interfaceName;
    }

    public function enterNode(Node $node) {
        parent::enterNode($node);

        // filtering class in this namespace :
        if ($node instanceof Namespace_) {
            $node->stmts = array_filter($node->stmts, function($node) {
                return ($node instanceof Class_) && ((string) $node->name === $this->className);
            });

            if (0 === count($node->stmts)) {
                throw new LogicException("{$this->className} class not found");
            }

            return $node;
        }

        // transforms the class into in an interface :
        if ($node instanceof Class_) {
            // Keep only methods and constants :
            $node->stmts = array_filter($node->stmts, function($node) {
                return (($node instanceof ClassMethod) && ((string) $node->name !== '__construct')) ||
                        ($node instanceof ClassConst);
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
