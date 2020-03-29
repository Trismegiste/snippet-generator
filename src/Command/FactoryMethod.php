<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description of FactoryMethod
 */
class FactoryMethod extends Command {

    protected function configure() {
        $this->setName('pattern:factory-method')
                ->setDescription('Generate a Factory Method for a concrete Class')
                ->addArgument('class', InputArgument::REQUIRED, "name of the Class file (without '.php'')")
                ->addArgument('source', InputArgument::OPTIONAL, 'The directory of your source', './src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $io->title('Factory Method generator');
        $className = $input->getArgument('class');

        /* @var $classFile \Symfony\Component\Finder\SplFileInfo */
        $classFile = $this->getHelper('file-picker')->pickFile($input, $output, $input->getArgument('source'), $className . '.php');

        $interfaceNew = $io->ask("Please choose a name for the new Interface abstracting $className class ", $className);
        $concreteNew = $io->ask("Please choose a name for the existing concrete class abstracted by $interfaceNew interface ", 'Concrete' . $className);
        $factoryMethod = $io->ask("Please choose a name for the new abstract Factory Method interface ", $interfaceNew . 'Factory');
        $concreteFactory = $io->ask("Please choose a name for the new concrete factory implementing $factoryMethod and that creates $concreteNew objects ", $concreteNew . 'Factory');

        $concreteContent = $classFile->getContents();
        // generation



        return 0;
    }

}
