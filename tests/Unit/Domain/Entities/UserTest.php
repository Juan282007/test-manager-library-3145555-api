<?php

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\User;
use App\Domain\Exceptions\InvalidUserRole;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_normalizes_username_to_lowercase_and_trimmed(): void
    {
        $user = new User('  Ana ', 'hashed-password', User::LIBRARIAN);

        $this->assertSame('ana', $user->username());
        $this->assertSame('librarian', $user->role());
        $this->assertSame('hashed-password', $user->passwordHash());
    }

    public function test_accepts_the_admin_role_for_provisioned_users(): void
    {
        $user = new User('admin', 'hashed-password', User::ADMIN);

        $this->assertSame('admin', $user->role());
    }

    public function test_rejects_an_unknown_role(): void
    {
        $this->expectException(InvalidUserRole::class);
        $this->expectExceptionMessage('A user role must be admin or librarian.');

        new User('ana', 'hashed-password', 'manager');
    }

    public function test_rejects_an_empty_username(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A username cannot be empty.');

        new User('   ', 'hashed-password', User::LIBRARIAN);
    }

    public function test_rejects_an_empty_password_hash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A password hash cannot be empty.');

        new User('ana', '', User::LIBRARIAN);
    }
}
