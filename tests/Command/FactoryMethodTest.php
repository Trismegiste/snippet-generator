<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Trismegiste\SnippetGenerator\Application;
use Trismegiste\SnippetGenerator\Command\FactoryMethod;

class FactoryMethodTest extends TestCase {

    public function testExecute() {
        $application = new Application();
        $application->setAutoExit(false);
        $command = new FactoryMethod();
        $application->add($command);
        $tester = new CommandTester($application->find('pattern:factory-method'));
        $tester->setInputs([
            'UserInterface',
            'ConcreteUser',
            'FactoryMethod',
            'ConcreteFactory'
        ]);

        $this->assertEquals(0, $tester->execute([
                    'source' => __DIR__ . '/../fixtures',
                    'class' => 'User'
        ]));

        $created = [
            __DIR__ . '/../fixtures/UserInterface.php',
            __DIR__ . '/../fixtures/ConcreteUser.php',
            __DIR__ . '/../fixtures/FactoryMethod.php',
            __DIR__ . '/../fixtures/ConcreteFactory.php'
        ];
        foreach ($created as $fch) {
            $this->assertFileExists($fch);
        }
        // instantiate since autoload-dev is configured for those classes :
        $fac = new \Fixtures\Demo\ConcreteFactory();
        $this->assertInstanceOf(\Fixtures\Demo\FactoryMethod::class, $fac);
        $obj = $fac->create('login', 'pwd');
        $this->assertInstanceOf(\Fixtures\Demo\UserInterface::class, $obj);
        $this->assertInstanceOf(\Fixtures\Demo\ConcreteUser::class, $obj);
        // clean
        foreach ($created as $fch) {
            unlink($fch);
        }
    }

}
