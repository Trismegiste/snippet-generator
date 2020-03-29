<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Trismegiste\Toolbox\MongoDb\RootImpl;

/**
 * Some conrete class to abstract
 */
class User implements UserInterface {

    use RootImpl;

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

}
