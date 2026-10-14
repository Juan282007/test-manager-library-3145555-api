<?php

namespace App\Application\Contracts;

use App\Domain\Entities\User;

interface UserRepository
{
    public function findByUsername(string $username): ?User;

    public function existsByUsername(string $username): bool;

    public function save(User $user): User;
}
