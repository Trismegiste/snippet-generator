<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Visitor;

use LogicException;
use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Generates the FactoryMethod Interface
 */
class FactoryMethodGenerator extends NodeVisitorAbstract
{

    protected $className;
    protected $factoryName;
    protected $objectInterface;

    /**
     * Ctor
     * @param string $className The original class name to refactor
     * @param string $factoryName The interface name of the factory
     * @param string $objectInterface The interface name of the Model
     */
    public function __construct(string $className, string $factoryName, string $objectInterface)
    {
        $this->className = $className;
        $this->factoryName = $factoryName;
        $this->objectInterface = $objectInterface;
    }

    public function enterNode(Node $node)
    {
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

        // transforms the class into a factory interface :
        if ($node instanceof Class_) {
            // Keep only constructor :
            $methods = array_filter($node->stmts, function($node) {
                return (($node instanceof ClassMethod) && ((string) $node->name == '__construct'));
            });
            if (count($methods)) {
                $constructor = array_pop($methods);
                $constructor->name = new Node\Identifier('create');
            } else {
                // no constructor, build empty create :
                $constructor = (new Method('create'))->makePublic()->getNode();
            }
            // sets the returned type by the factory :
            $constructor->returnType = new Node\Identifier($this->objectInterface);
            // creating the interface Node :
            $newInterface = new Interface_($this->factoryName, ['stmts' => [$constructor]]);

            return $newInterface;
        }

        // removing implementation in methods (currently only "create")
        if ($node instanceof ClassMethod) {
            $node->stmts = null;
        }
    }

}
