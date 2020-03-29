<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator\Command\Helper;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Description of FinderHelper
 */
class FilePicker extends Helper {

    public function getName(): string {
        return 'file-picker';
    }

    public function pickFile(InputInterface $input, OutputInterface $output, string $folder, string $pattern): SplFileInfo {
        $questionHelper = $this->getHelperSet()->get('question');

        $iter = new Finder();
        $iter->in($folder)->name($pattern)->files();

        $found = iterator_to_array($iter);
        switch (count($found)) {
            case 0:
                throw new RuntimeException("No file matching '$pattern' were found");
            case 1:
                /* @var $interfaceFile SplFileInfo */
                $pickedOne = array_pop($found);
                break;
            default :
                $question = new ChoiceQuestion("There are multiple files with the pattern '$pattern', please select the one you refer to ",
                        array_keys($found));
                $choice = $questionHelper->ask($input, $output, $question);
                $pickedOne = $found[$choice];
        }

        return $pickedOne;
    }

}
