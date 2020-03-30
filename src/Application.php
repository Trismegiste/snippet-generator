<?php

/*
 * trismegiste/snippet-generator
 */

namespace Trismegiste\SnippetGenerator;

use Symfony\Component\Console\Helper\HelperSet;
use Trismegiste\SnippetGenerator\Command\Helper\FilePicker;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application for this CLI
 */
class Application extends BaseApplication
{

    /**
     * Adding the FilePicker helper for all Commands
     * @return HelperSet
     */
    protected function getDefaultHelperSet(): HelperSet
    {
        $hs = parent::getDefaultHelperSet();
        $hs->set(new FilePicker());

        return $hs;
    }

}
