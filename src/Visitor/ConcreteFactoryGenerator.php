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
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Generates the implementation of FactoryMethod Interface
 */
class ConcreteFactoryGenerator extends NodeVisitorAbstract {

    protected $className;
    protected $factoryName;
    protected $objectInterface;
    protected $factoryInterfaceName;
    protected $model;

    public function __construct(string $className, string $model, string $objectInterface, string $factoryName, string $factoryInterfaceName) {
        $this->className = $className;
        $this->factoryName = $factoryName;
        $this->objectInterface = $objectInterface;
        $this->factoryInterfaceName = $factoryInterfaceName;
        $this->model = $model;
    }

    public function enterNode(Node $node) {
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

        // transforms the class into a concrete factory :
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
            // renaming the Node and inherits from the factory method interface :
            $node->name = new Node\Identifier($this->factoryName);
            $node->stmts = [$constructor];
            $node->implements = [new Node\Identifier($this->factoryInterfaceName)];

            return $node;
        }

        // adding creation of a concrete object
        if ($node instanceof ClassMethod) {
            $args = [];
            foreach ($node->params as $param) {
                $args[] = new Node\Expr\Variable($param->var->name);
            }

            $node->stmts = [new Node\Stmt\Return_(new Node\Expr\New_(new Node\Name($this->model), $args))];
        }
    }

}
