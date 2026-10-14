<?php

namespace App\Domain\Entities;

use App\Domain\Exceptions\InvalidUserRole;
use InvalidArgumentException;

final class User
{
    public const ADMIN = 'admin';

    public const LIBRARIAN = 'librarian';

    private string $username;

    private string $passwordHash;

    private string $role;

    public function __construct(string $username, string $passwordHash, string $role)
    {
        $normalizedUsername = mb_strtolower(trim($username));
        $normalizedRole = mb_strtolower(trim($role));

        if ($normalizedUsername === '') {
            throw new InvalidArgumentException('A username cannot be empty.');
        }

        if ($passwordHash === '') {
            throw new InvalidArgumentException('A password hash cannot be empty.');
        }

        if (! in_array($normalizedRole, [self::ADMIN, self::LIBRARIAN], true)) {
            throw new InvalidUserRole('A user role must be admin or librarian.');
        }

        $this->username = $normalizedUsername;
        $this->passwordHash = $passwordHash;
        $this->role = $normalizedRole;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): string
    {
        return $this->role;
    }
}
