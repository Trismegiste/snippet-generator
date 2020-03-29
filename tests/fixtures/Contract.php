<?php

/*
 * Example of an interface
 */

namespace Trismegiste\H2G2;

interface Contract {

    const answer = 42;

    public function search(array $filter = [], array $excludedField = [], string &$descendingSortField = null): \Iterator;

    public function load(string $pk): Root;

    public function save($documentOrArray): void;
}

class Dummy {
    
}

$a = 1;
