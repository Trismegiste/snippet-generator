<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Command;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;
use Trismegiste\SnippetGenerator\Visitor\ClassInheritsFromPublicInterface;
use Trismegiste\SnippetGenerator\Visitor\ClassToInterfaceGenerator;
use Trismegiste\SnippetGenerator\Visitor\ConcreteFactoryGenerator;
use Trismegiste\SnippetGenerator\Visitor\FactoryMethodGenerator;

/**
 * Description of FactoryMethod
 */
class FactoryMethod extends Command {

    protected $parser;
    protected $printer;

    protected function configure() {
        $this->setName('pattern:factory-method')
                ->setDescription('Generate a Factory Method for a concrete Class')
                ->addArgument('class', InputArgument::REQUIRED, "name of the Class file (without '.php'')")
                ->addArgument('source', InputArgument::OPTIONAL, 'The directory of your source', './src');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->prettyPrinter = new Standard();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $io->title('Factory Method generator');
        $className = $input->getArgument('class');

        /* @var $classFile SplFileInfo */
        $classFile = $this->getHelper('file-picker')->pickFile($input, $output, $input->getArgument('source'), $className . '.php');

        $interfaceNew = $io->ask("Please choose a name for the new Interface abstracting $className class ", $className);
        $concreteNew = $io->ask("Please choose a new name for the existing concrete class abstracted by $interfaceNew interface ", 'Concrete' . $className);
        $factoryMethod = $io->ask("Please choose a name for the new Factory Method interface ", $interfaceNew . 'Factory');
        $concreteFactory = $io->ask("Please choose a name for the concrete factory implementing $factoryMethod and that creates $concreteNew objects ", $concreteNew . 'Factory');

        $source = $classFile->getContents();
        // generation
        echo $this->generateModelInterface($source, $className, $interfaceNew);
        echo $this->updateModelClass($source, $className, $interfaceNew, $concreteNew);
        echo $this->generateFactoryInterface($source, $className, $factoryMethod, $interfaceNew);
        echo $this->generateConcreteFactory($source, $className, $interfaceNew, $factoryMethod, $concreteFactory);

        return 0;
    }

    private function generateModelInterface(string $source, string $className, string $interfaceName): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ClassToInterfaceGenerator($className, $interfaceName));
            $ast = $traverser->traverse($ast);
        } catch (Error $error) {
            throw new RuntimeException("Unable to generate $interfaceName", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

    private function updateModelClass(string $source, string $className, string $interfaceName, $newClassName): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ClassInheritsFromPublicInterface($className, $interfaceName, $newClassName));
            $ast = $traverser->traverse($ast);
        } catch (Error $error) {
            throw new RuntimeException("Unable to update $className into $newClassName", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

    private function generateFactoryInterface(string $source, string $className, string $factoryName, string $interfaceName): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new FactoryMethodGenerator($className, $factoryName, $interfaceName));
            $ast = $traverser->traverse($ast);
        } catch (Error $error) {
            throw new RuntimeException("Unable to update $className into $newClassName", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

    private function generateConcreteFactory(string $source, string $className, string $interfaceName, string $factoryName, string $concreteFactory): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ConcreteFactoryGenerator($className, $interfaceName, $concreteFactory, $factoryName));
            $ast = $traverser->traverse($ast);
        } catch (Error $error) {
            throw new RuntimeException("Unable to update $className into $newClassName", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

}
