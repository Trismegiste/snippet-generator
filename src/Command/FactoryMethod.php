<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of FactoryMethod
 */
class FactoryMethod extends Command {

    protected function configure() {
        $this->setName('pattern:factory-method')
                ->setDescription('Generate a Factory Method for a concrete Class')
                ->addArgument('class', InputArgument::REQUIRED, "name of the Class files (without '.php'')")
                ->addArgument('source', InputArgument::OPTIONAL, 'The directory of your source', './src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        
    }

}
