<?php

class ClassToInterfaceGeneratorTest extends \Tests\Visitor\VisitorTestCase {

    protected function generate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new \Trismegiste\SnippetGenerator\Visitor\ClassToInterfaceGenerator($name, $name . 'Interface'));
        return $this->traverser->traverse($ast);
    }

    public function testGeneration() {
        $ast = $this->generate('Simple', 'namespace App; class Simple { public function someService(); }');
        echo $this->toPhp($ast);
    }

}
