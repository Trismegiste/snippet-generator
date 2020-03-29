<?php

use Tests\Visitor\VisitorTestCase;
use Trismegiste\SnippetGenerator\Visitor\ClassToInterfaceGenerator;

class ClassToInterfaceGeneratorTest extends VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new ClassToInterfaceGenerator($name, $name . 'Interface'));
        return $this->traverser->traverse($ast);
    }

    public function testGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { public function someService(); }');
        $this->assertCount(1, $ast);
        $ns = array_pop($ast);
        $this->assertCount(1, $ns->stmts);
        $interfaceNode = array_pop($ns->stmts);
        $this->assertInstanceOf(\PhpParser\Node\Stmt\Interface_::class, $interfaceNode);
        $this->assertEquals('SimpleInterface', (string) $interfaceNode->name);
    }

}
