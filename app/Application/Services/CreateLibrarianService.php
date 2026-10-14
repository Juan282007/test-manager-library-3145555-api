<?php

namespace App\Application\Services;

use App\Application\Contracts\PasswordHasher;
use App\Application\Contracts\UserRepository;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UsernameAlreadyTaken;

final class CreateLibrarianService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
    ) {}

    public function create(string $username, string $password): User
    {
        if ($this->users->existsByUsername($username)) {
            throw new UsernameAlreadyTaken('The username is already in use.');
        }

        $user = new User(
            $username,
            $this->passwordHasher->hash($password),
            User::LIBRARIAN,
        );

        return $this->users->save($user);
    }
}
