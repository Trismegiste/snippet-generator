<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Command;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
                ->addOption('dry', null, InputOption::VALUE_NONE, "No writing, only test")
                ->setHelp(file_get_contents(__DIR__ . '/../../doc/FactoryMethod.md'));
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

        $modelInterface = $io->ask(self::nameMsg . "new Interface abstracting $className class ", $className);
        $modelConcrete = $io->ask(self::nameMsg . "existing concrete class abstracted by $modelInterface interface ", 'Concrete' . $className);
        $factoryInterface = $io->ask(self::nameMsg . "new Factory Method interface ", $modelInterface . 'Factory');
        $factoryConcrete = $io->ask(self::nameMsg . "concrete factory implementing $factoryInterface and that creates $modelConcrete objects ", $modelConcrete . 'Factory');

        $source = $classFile->getContents();
        // generation
        $this->write($io, $classFile->getPath(), $modelInterface, $this->generateModelInterface($source, $className, $modelInterface));
        $this->write($io, $classFile->getPath(), $modelConcrete, $this->updateModelClass($source, $className, $modelInterface, $modelConcrete));
        $this->write($io, $classFile->getPath(), $factoryInterface, $this->generateFactoryInterface($source, $className, $factoryInterface, $modelInterface));
        $this->write($io, $classFile->getPath(), $factoryConcrete, $this->generateConcreteFactory($source, $className, $modelConcrete, $modelInterface, $factoryConcrete, $factoryInterface));

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

    private function generateModelInterface(string $source, string $className, string $modelInterface): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ClassToInterfaceGenerator($className, $modelInterface));
            $ast = $traverser->traverse($ast);

            return $this->prettyPrinter->prettyPrintFile($ast);
        } catch (\Exception $error) {
            throw new RuntimeException("Unable to generate $modelInterface.php", $error->getCode(), $error);
        }
    }

    private function updateModelClass(string $source, string $className, string $modelInterface, $modelConcrete): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ClassInheritsFromPublicInterface($className, $modelInterface, $modelConcrete));
            $ast = $traverser->traverse($ast);

            return $this->prettyPrinter->prettyPrintFile($ast);
        } catch (\Exception $error) {
            throw new RuntimeException("Unable to update $className into $modelConcrete.php", $error->getCode(), $error);
        }
    }

    private function generateFactoryInterface(string $source, string $className, string $factoryInterface, string $modelInterface): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new FactoryMethodGenerator($className, $factoryInterface, $modelInterface));
            $ast = $traverser->traverse($ast);

            return $this->prettyPrinter->prettyPrintFile($ast);
        } catch (\Exception $error) {
            throw new RuntimeException("Unable to generate $factoryInterface.php", $error->getCode(), $error);
        }
    }

    private function generateConcreteFactory(string $source, string $className, $modelConcrete, $modelInterface, $factoryConcrete, $factoryInterface): string {
        try {
            $ast = $this->parser->parse($source);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ConcreteFactoryGenerator($className, $modelConcrete, $modelInterface, $factoryConcrete, $factoryInterface));
            $ast = $traverser->traverse($ast);

            return $this->prettyPrinter->prettyPrintFile($ast);
        } catch (\Exception $error) {
            throw new RuntimeException("Unable to generate $factoryConcrete.php", $error->getCode(), $error);
        }
    }

}
