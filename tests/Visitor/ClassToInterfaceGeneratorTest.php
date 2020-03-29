<?php

use PhpParser\Node\Stmt\Interface_;
use Tests\Visitor\VisitorTestCase;
use Trismegiste\SnippetGenerator\Visitor\ClassToInterfaceGenerator;

class ClassToInterfaceGeneratorTest extends VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new ClassToInterfaceGenerator($name, $name . 'Interface'));
        return $this->traverser->traverse($ast);
    }

    public function testSimpleGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { public function someService(); }');
        $this->assertCount(1, $ast);
        $ns = array_pop($ast);
        $this->assertCount(1, $ns->stmts);
        $interfaceNode = array_pop($ns->stmts);
        $this->assertInstanceOf(Interface_::class, $interfaceNode);
        $this->assertEquals('SimpleInterface', (string) $interfaceNode->name);
        $this->assertCount(1, $interfaceNode->stmts);
        $methodNode = array_pop($interfaceNode->stmts);
        $this->assertEquals('someService', (string) $methodNode->name);
        $this->assertNull($methodNode->stmts);
    }

    public function testRealCase() {
        $content = file_get_contents(__DIR__ . '/../fixtures/User.php');
        $ast = $this->generate('User', substr($content, 5));
        $this->assertStringContainsString('42', $this->toPhp($ast));
        $this->assertCount(1, $ast[0]->stmts);
        $inter = array_pop($ast[0]->stmts);
        $this->assertCount(3, $inter->stmts);  // 1 const + 2 methods
    }

    public function testNotFound() {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not found');
        $this->generate('Yolo', 'namespace A; class B {} ');
    }

}
