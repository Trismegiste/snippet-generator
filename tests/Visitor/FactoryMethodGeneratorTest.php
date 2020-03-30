<?php

use PhpParser\Node\Stmt\Interface_;
use Tests\Visitor\VisitorTestCase;
use Trismegiste\SnippetGenerator\Visitor\FactoryMethodGenerator;

class FactoryMethodGeneratorTest extends VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new FactoryMethodGenerator($name, 'Factory', $name . 'Interface'));
        return $this->traverser->traverse($ast);
    }

    public function testSimpleGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { public function noConstructor(); }');
        $this->assertCount(1, $ast);
        $ns = array_pop($ast);
        $this->assertCount(1, $ns->stmts);
        $interfaceNode = array_pop($ns->stmts);
        $this->assertInstanceOf(Interface_::class, $interfaceNode);
        $this->assertEquals('Factory', (string) $interfaceNode->name);
        $this->assertCount(1, $interfaceNode->stmts); // one creation method
        $methodNode = array_pop($interfaceNode->stmts);
        $this->assertInstanceOf(PhpParser\Node\Stmt\ClassMethod::class, $methodNode);
        $this->assertEquals('create', (string) $methodNode->name);
        $this->assertNull($methodNode->stmts);
        $this->assertCount(0, $methodNode->params);
    }

    public function testRealCase() {
        $content = file_get_contents(__DIR__ . '/../fixtures/User.php');
        $ast = $this->generate('User', substr($content, 5));
        echo $this->dump($ast);
        echo $this->toPhp($ast);
        $this->assertStringNotContainsString('42', $this->toPhp($ast));
        $this->assertCount(1, $ast[0]->stmts);
        $inter = array_pop($ast[0]->stmts);
        $this->assertCount(1, $inter->stmts); // 1 method
        $methodNode = array_pop($inter->stmts);
        $this->assertInstanceOf(PhpParser\Node\Stmt\ClassMethod::class, $methodNode);
        $this->assertEquals('create', (string) $methodNode->name);
        $this->assertNull($methodNode->stmts);
        $this->assertCount(2, $methodNode->params);
    }

    public function testNotFound() {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not found');
        $this->generate('Yolo', 'namespace A; class B {} ');
    }

}
