<?php

namespace App\Application\Contracts;

interface PasswordHasher
{
    public function hash(string $password): string;

    public function verify(string $password, string $passwordHash): bool;
}
