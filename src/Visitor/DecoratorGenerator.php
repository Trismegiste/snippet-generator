<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitor\NameResolver;

/**
 * DecoratorGenerator transforms an interface into an implementation a Decorator according to Design Pattern Decorator
 */
class DecoratorGenerator extends NameResolver {

    protected $interfaceName;
    protected $decoratorName;

    public function __construct(string $interfaceName, string $decoratorName) {
        parent::__construct();
        $this->interfaceName = $interfaceName;
        $this->decoratorName = $decoratorName;
    }

    public function enterNode(Node $node) {
        parent::enterNode($node);

        // filtering interface inn this namespace :
        if ($node instanceof Node\Stmt\Namespace_) {
            $node->stmts = array_filter($node->stmts, function($node) {
                return ($node instanceof Interface_) && ((string) $node->name === $this->interfaceName);
            });

            if (0 === count($node->stmts)) {
                throw new \LogicException("Could not find any interface named {$this->interfaceName}");
            }

            return $node;
        }

        // transforms the interface into in a class :
        if ($node instanceof Interface_) {
            $decorator = new Node\Stmt\Class_($this->decoratorName, [
                'implements' => [$node->name],
                'stmts' => $node->stmts
                    ], [
                'comments' => [new \PhpParser\Comment\Doc('/** This is a Decorator for ' . $node->namespacedName . ' */')]
            ]);
            array_unshift($decorator->stmts, new Node\Stmt\Property(Node\Stmt\Class_::MODIFIER_PROTECTED, [new Node\Stmt\PropertyProperty('decorated')]));

            return $decorator;
        }

        // implementing decoration in methods :
        if ($node instanceof Node\Stmt\ClassMethod) {

            $args = [];
            foreach ($node->params as $param) {
                $args[] = new Node\Expr\Variable($param->var->name);
            }

            $methodCall = new Node\Expr\MethodCall(
                    new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), 'decorated'),
                    $node->name, $args);

            if ($node->returnType == 'void') {
                $node->stmts[0] = new Node\Stmt\Expression($methodCall);
            } else {
                $node->stmts[0] = new Node\Stmt\Return_($methodCall);
            }
            $node->setAttribute('comments', null);
        }
    }

    public function leaveNode(Node $node) {
        parent::leaveNode($node);

        // adding the ctor :
        if ($node instanceof Node\Stmt\Class_) {
            // Add constructor
            $constructor = new Node\Stmt\ClassMethod('__construct', [
                'params' => [
                    new Node\Param(new Node\Expr\Variable('decorated'), null, $node->implements[0]->name)
                ],
                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC
            ]);
            $constructor->stmts[0] = new Node\Stmt\Expression(new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), 'decorated'),
                            new Node\Expr\Variable('decorated')
            ));
            $node->stmts[] = $constructor;

            // Remove ClassConst
            $node->stmts = array_filter($node->stmts, function($node) {
                return !($node instanceof Node\Stmt\ClassConst);
            });

            return $node;
        }
    }

}
