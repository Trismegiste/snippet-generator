<?php

use Trismegiste\SnippetGenerator\Application;
use Trismegiste\SnippetGenerator\Command\Decorator;

/*
 * trismegiste/design-pattern-snippet
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application('Snippet Generator');
// adding commands :
$app->add(new Decorator);

$app->run();
