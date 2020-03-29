<?php

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
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

    protected function decorate(string $name, string $example) {
        $ast = $this->parser->parse('<?php ' . $example);
        $this->traverser->addVisitor(new DecoratorGenerator($name, $name . 'Decorator'));
        return $this->traverser->traverse($ast);
    }

    public function getSimpleInterface() {
        return [
            ['Simple', 'namespace Nothing; interface Simple {}'],
            ['Simple', 'namespace Nothing; interface Simple {} class IgnoredAfter {}'],
            ['Simple', 'namespace Nothing; use A; class IgnoredBefore {} interface Simple {} $a=1;']
        ];
    }

    /** @dataProvider getSimpleInterface */
    public function testEmbedding(string $name, string $example) {
        $decorator = $this->decorate($name, $example);

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

    public function testNotFound() {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not found');
        $this->decorate('Yolo', 'namespace A; interface B {} ');
    }

    public function getWithMethod() {
        return [
            ['Simple', 'namespace Nothing; interface Simple { public function meth(); }'],
            ['Simple', 'namespace Nothing; interface Simple { function meth(); }'],
            ['Simple', 'namespace Nothing; interface Simple { function meth($a); }'],
            ['Simple', 'namespace Nothing; interface Simple { function meth(int $a); }'],
            ['Simple', 'namespace Nothing; interface Simple { function meth(int &$a); }'],
            ['Simple', 'namespace Nothing; interface Simple { function meth(\stdClass $a); }'],
        ];
    }

    /** @dataProvider getWithMethod */
    public function testImplementingWithReturn(string $name, string $example) {
        $decorator = $this->decorate($name, $example);

        $methNode = $decorator[0]->stmts[0]->stmts[2];
        $this->assertEquals('meth', (string) $methNode->name);
        $this->assertTrue($methNode->isPublic());
        $this->assertCount(1, $methNode->stmts);
        $this->assertInstanceOf(Return_::class, $methNode->stmts[0]);
    }

    public function getWithoutReturn() {
        return [
            ['Simple', 'namespace Nothing; interface Simple { public function meth(): void; }'],
        ];
    }

    /** @dataProvider getWithoutReturn */
    public function testImplementingWithoutReturn(string $name, string $example) {
        $decorator = $this->decorate($name, $example);

        $methNode = $decorator[0]->stmts[0]->stmts[2];
        $this->assertCount(1, $methNode->stmts);
        $this->assertInstanceOf(\PhpParser\Node\Stmt\Expression::class, $methNode->stmts[0]);
    }

}
