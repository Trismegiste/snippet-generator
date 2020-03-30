<?php

namespace Fixtures\Demo;

use Fixtures\Demo\DemoTrait;

/**
 * Some conrete class to abstract
 */
class User {

    use DemoTrait;

    const AUTH = 42;

    private $username;
    private $roles = [];
    private $password;

    public function __construct(string $user, string $pwd) {
        $this->username = $user;
        $this->password = $pwd;
        $this->roles[] = 'ROLE_ADMIN';
    }

    /**
     * A visual identifier that represents this user.
     */
    public function getUsername(): string {
        return (string) $this->username;
    }

    /**
     * Gets the password
     */
    public function getPassword(): string {
        return (string) $this->password;
    }

    protected function notExported() {
        
    }

}
