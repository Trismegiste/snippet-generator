<?php

require_once __DIR__ . '/vendor/autoload.php';

/*
 * trismegiste/design-pattern-snippet
 */

use Trismegiste\SnippetGenerator\Application;
use Trismegiste\SnippetGenerator\Command\Decorator;
use Trismegiste\SnippetGenerator\Command\FactoryMethod;

$app = new Application('Snippet Generator');
// adding commands :
$app->add(new Decorator());
$app->add(new FactoryMethod());

$app->run();
