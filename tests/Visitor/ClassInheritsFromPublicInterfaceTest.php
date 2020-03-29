<?php

use PhpParser\Node\Stmt\Class_;
use Tests\Visitor\VisitorTestCase;
use Trismegiste\SnippetGenerator\Visitor\ClassInheritsFromPublicInterface;

class ClassInheritsFromPublicInterfaceTest extends VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new ClassInheritsFromPublicInterface($name, $name . 'Interface', 'Concrete' . $name));
        return $this->traverser->traverse($ast);
    }

    public function testSimpleGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { const answer=42; public function someService(); }');
        $this->assertCount(1, $ast);
        $ns = array_pop($ast);
        $this->assertCount(1, $ns->stmts);
        $classNode = array_pop($ns->stmts);
        $this->assertInstanceOf(Class_::class, $classNode);
        $this->assertEquals('ConcreteSimple', (string) $classNode->name);
        $this->assertCount(1, $classNode->stmts);
        $this->assertEquals(['SimpleInterface'], $classNode->implements);
    }

    public function testRealCase() {
        $content = file_get_contents(__DIR__ . '/../fixtures/User.php');
        $ast = $this->generate('User', substr($content, 5));
        $this->assertStringNotContainsString('42', $this->toPhp($ast));  // const is now missing
        $this->assertCount(2, $ast[0]->stmts); // use and class statements
        $classNode = $ast[0]->stmts[1];
        $this->assertInstanceOf(Class_::class, $classNode);
        $this->assertEquals(['UserInterface'], $classNode->implements);
        $this->assertCount(8, $classNode->stmts);  // use + 3 properties + 4 methods
    }

}
