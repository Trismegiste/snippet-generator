<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator;

use Symfony\Component\Console\Helper\HelperSet;
use Trismegiste\SnippetGenerator\Command\Helper\FilePicker;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Description of Application
 */
class Application extends BaseApplication {

    protected function getDefaultHelperSet(): HelperSet {
        $hs = parent::getDefaultHelperSet();
        $hs->set(new FilePicker());

        return $hs;
    }

}
