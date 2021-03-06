<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Trismegiste\SnippetGenerator\Application;
use Trismegiste\SnippetGenerator\Command\Decorator;

class DecoratorTest extends TestCase {

    public function testExecute() {
        $application = new Application();
        $application->setAutoExit(false);
        $command = new Decorator();
        $application->add($command);
        $tester = new CommandTester($application->find('pattern:decorator'));

        $this->assertEquals(0, $tester->execute([
                    'source' => __DIR__ . '/../fixtures',
                    'interface' => 'Contract'
        ]));

        $created = __DIR__ . '/../fixtures/ContractDecorator.php';
        $this->assertFileExists($created);
        unlink($created);
    }

    public function testFailure() {
        $application = new Application();
        $application->setAutoExit(false);
        $command = new Decorator();
        $application->add($command);
        $tester = new CommandTester($application->find('pattern:decorator'));

        $this->expectException(Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage("Unable");
        $this->assertEquals(0, $tester->execute([
                    'source' => __DIR__ . '/../fixtures',
                    'interface' => 'Invalid'
        ]));
    }

}
