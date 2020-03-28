<?php

/*
 * trismegiste/design-pattern-snippet
 */

namespace Trismegiste\DesignPattern\SnippetGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

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
                $interfaceFile = array_pop($found);
                break;
            default :
                $interfaceFile = $io->choice("There are multiple files that name $interfaceName, which one do you refer", $found);
        }

        $io->section("Generation of a Decorator for $interfaceName located in $interfaceFile");

        return 0;
    }

}
