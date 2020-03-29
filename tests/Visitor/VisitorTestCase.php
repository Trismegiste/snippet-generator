<?php

/*
 * trismegiste/snippet-generator
 */

namespace Tests\Visitor;

use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * Description of VisitorTestCase
 */
class VisitorTestCase extends TestCase {

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

    protected function toPhp($ast) {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile($ast);
    }

}
