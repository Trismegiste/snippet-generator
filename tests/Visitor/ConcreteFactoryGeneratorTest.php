<?php

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Tests\Visitor\VisitorTestCase;
use Trismegiste\SnippetGenerator\Visitor\ConcreteFactoryGenerator;

class ConcreteFactoryGeneratorTest extends VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new ConcreteFactoryGenerator($name, 'Concrete' . $name, $name . 'Interface', 'ConcreteFactory', 'Factory'));
        return $this->traverser->traverse($ast);
    }

    public function testSimpleGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { public function noConstructor(); }');
        $this->assertCount(1, $ast);
        $ns = array_pop($ast);
        $this->assertCount(1, $ns->stmts);
        $classNode = array_pop($ns->stmts);
        $this->assertInstanceOf(Class_::class, $classNode);
        $this->assertEquals('ConcreteFactory', (string) $classNode->name);
        $this->assertCount(1, $classNode->stmts); // one creation method
        $methodNode = array_pop($classNode->stmts);
        $this->assertInstanceOf(ClassMethod::class, $methodNode);
        $this->assertEquals('create', (string) $methodNode->name);
        $this->assertNotNull($methodNode->stmts);
        $this->assertCount(0, $methodNode->params);
    }

    public function testRealCase() {
        $content = file_get_contents(__DIR__ . '/../fixtures/User.php');
        $ast = $this->generate('User', substr($content, 5));
        $this->assertStringNotContainsString('42', $this->toPhp($ast));
        $this->assertCount(1, $ast[0]->stmts);
        $factory = array_pop($ast[0]->stmts);
        $this->assertCount(1, $factory->stmts); // 1 method
        $methodNode = array_pop($factory->stmts);
        $this->assertInstanceOf(ClassMethod::class, $methodNode);
        $this->assertEquals('create', (string) $methodNode->name);
        $this->assertNotNull($methodNode->stmts);
        $this->assertCount(2, $methodNode->params);
    }

    public function testNotFound() {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not found');
        $this->generate('Yolo', 'namespace A; class B {} ');
    }

}
