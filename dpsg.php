<?php

use Symfony\Component\Console\Application;
use Trismegiste\SnippetGenerator\Command\Decorator;

/*
 * trismegiste/design-pattern-snippet
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application('Design Pattern Snippet Generator');
$app->add(new Decorator);

$app->run();
