<?php

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Trismegiste\SnippetGenerator\Visitor\DecoratorGenerator;

class DecoratorGeneratorTest extends TestCase {

    protected $parser;
    protected $traverser;
    protected $dumper;

    protected function setUp(): void {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser();
        $this->dumper = new NodeDumper;
    }

    protected function tearDown(): void {
        unset($this->parser);
        unset($this->traverser);
        unset($this->dumper);
    }

    protected function dump($node) {
        return $this->dumper->dump($node);
    }

    /** @dataProvider getSimpleInterface */
    public function testEmbedding(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new DecoratorGenerator($name, $name . 'Decorator'));
        $decorator = $this->traverser->traverse($ast);

        $this->assertCount(1, $decorator);
        $ns = array_pop($decorator);
        $this->assertCount(1, $ns->stmts);
        $classNode = array_pop($ns->stmts);
        $this->assertInstanceOf(Class_::class, $classNode);
        $this->assertEquals($name . 'Decorator', (string) $classNode->name);
        $this->assertEquals($name, (string) $classNode->implements[0]);
        $this->assertInstanceOf(Property::class, $classNode->stmts[0]);
        $this->assertInstanceOf(ClassMethod::class, $classNode->stmts[1]);
        $this->assertEquals('__construct', (string) $classNode->stmts[1]->name);
    }

    public function getSimpleInterface() {
        return [
            ['Simple', 'namespace Nothing; interface Simple {}'],
            ['Simple', 'namespace Nothing; interface Simple {} class IgnoredAfter {}']
        ];
    }

}
