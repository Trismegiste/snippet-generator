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

    protected const nameMsg = "Please choose a name for the ";

    protected $parser;
    protected $printer;
    protected $dryRun = false;

    protected function configure() {
        $this->setName('pattern:factory-method')
                ->setDescription('Generate a Factory Method for a concrete Class')
                ->addArgument('class', InputArgument::REQUIRED, "name of the Class file (without '.php'')")
                ->addArgument('source', InputArgument::OPTIONAL, 'The directory of your source', './src')
                ->addOption('dry', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, "No writing, only test");
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->prettyPrinter = new Standard();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->dryRun = $input->getOption("dry");
        $io = new SymfonyStyle($input, $output);
        $io->title('Factory Method generator');
        $className = $input->getArgument('class');

        /* @var $classFile SplFileInfo */
        $classFile = $this->getHelper('file-picker')->pickFile($input, $output, $input->getArgument('source'), $className . '.php');

        $interfaceNew = $io->ask(self::nameMsg . "new Interface abstracting $className class ", $className);
        $concreteNew = $io->ask(self::nameMsg . "existing concrete class abstracted by $interfaceNew interface ", 'Concrete' . $className);
        $factoryMethod = $io->ask(self::nameMsg . "new Factory Method interface ", $interfaceNew . 'Factory');
        $concreteFactory = $io->ask(self::nameMsg . "concrete factory implementing $factoryMethod and that creates $concreteNew objects ", $concreteNew . 'Factory');

        $source = $classFile->getContents();
        // generation
        $this->write($io, $classFile->getPath(), $interfaceNew, $this->generateModelInterface($source, $className, $interfaceNew));
        $this->write($io, $classFile->getPath(), $concreteNew, $this->updateModelClass($source, $className, $interfaceNew, $concreteNew));
        $this->write($io, $classFile->getPath(), $factoryMethod, $this->generateFactoryInterface($source, $className, $factoryMethod, $interfaceNew));
        $this->write($io, $classFile->getPath(), $concreteFactory, $this->generateConcreteFactory($source, $className, $concreteNew, $interfaceNew, $factoryMethod, $concreteFactory));

        return 0;
    }

    private function write(SymfonyStyle $io, string $path, string $filename, string $content) {
        $target = "$path/$filename.php";
        $io->section("Generation of $target");
        if (!$this->dryRun) {
            file_put_contents($target, $content);
        }
        $io->success("$target created");
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
            throw new RuntimeException("Unable to generate $factoryName", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

    private function generateConcreteFactory(string $source, string $className, string $model, string $interfaceName, string $factoryName, string $concreteFactory): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ConcreteFactoryGenerator($className, $model, $interfaceName, $concreteFactory, $factoryName));
            $ast = $traverser->traverse($ast);
        } catch (Error $error) {
            throw new RuntimeException("Unable to generate $concreteFactory", $error->getCode(), $error->getMessage());
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }

}
