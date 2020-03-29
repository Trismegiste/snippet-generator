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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Trismegiste\SnippetGenerator\Visitor\DecoratorGenerator;

/**
 * Description of Decorator
 */
class Decorator extends Command {

    protected function configure() {
        $this->setName('dp:decorator')
                ->setDescription('Generate a Decorator for an Interface')
                ->addArgument('interface', InputArgument::REQUIRED, "name of the Interface files (without '.php'')")
                ->addArgument('source', InputArgument::OPTIONAL, 'The directory of your source', './src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $io->title('Decorator generator');
        $interfaceName = $input->getArgument('interface');

        $iter = new Finder();
        $iter->in($input->getArgument('source'))
                ->name($interfaceName . '.php')
                ->files();

        $found = iterator_to_array($iter);
        switch (count($found)) {
            case 0:
                throw new RuntimeException("$interfaceName was not found");
            case 1:
                /* @var $interfaceFile SplFileInfo */
                $interfaceFile = array_pop($found);
                break;
            default :
                $choice = $io->choice("There are multiple files with the name '$interfaceName', please select the one you refer to ", array_keys($found));
                $interfaceFile = $found[$choice];
        }

        $io->section("Generation of a Decorator for $interfaceName located in $interfaceFile");

        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        try {
            $ast = $parser->parse($interfaceFile->getContents());
        } catch (Error $error) {
            throw new RuntimeException("Unable to parse $interfaceFile", $error->getCode(), $error->getMessage());
        }

        $decoratorName = $interfaceName . 'Decorator';
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new DecoratorGenerator($interfaceName, $decoratorName));
        $ast = $traverser->traverse($ast);

        $prettyPrinter = new Standard;
        file_put_contents($interfaceFile->getPath() . '/' . $decoratorName . '.php', $prettyPrinter->prettyPrintFile($ast));

        return 0;
    }

}
