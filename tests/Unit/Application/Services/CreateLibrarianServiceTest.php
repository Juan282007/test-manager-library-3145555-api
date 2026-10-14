<?php

namespace Tests\Unit\Application\Services;

use App\Application\Contracts\PasswordHasher;
use App\Application\Contracts\UserRepository;
use App\Application\Services\CreateLibrarianService;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UsernameAlreadyTaken;
use PHPUnit\Framework\TestCase;

class CreateLibrarianServiceTest extends TestCase
{
    public function test_creates_a_librarian_with_a_hashed_password_and_normalized_username(): void
    {
        $users = new CreateUserRepository;
        $service = new CreateLibrarianService($users, new CreatePasswordHasher);

        $user = $service->create(' Ana ', 'secret');

        $this->assertSame('ana', $user->username());
        $this->assertSame('librarian', $user->role());
        $this->assertSame('hashed-secret', $user->passwordHash());
        $this->assertSame($user, $users->savedUser);
    }

    public function test_rejects_a_duplicate_username_before_hashing_or_saving(): void
    {
        $users = new CreateUserRepository(new User('ana', 'hashed-password', User::LIBRARIAN));
        $hasher = new CreatePasswordHasher;
        $service = new CreateLibrarianService($users, $hasher);

        $this->expectException(UsernameAlreadyTaken::class);
        $this->expectExceptionMessage('The username is already in use.');

        $service->create(' Ana ', 'secret');
    }
}

final class CreateUserRepository implements UserRepository
{
    public ?User $savedUser = null;

    public function __construct(private ?User $existingUser = null) {}

    public function findByUsername(string $username): ?User
    {
        return $this->existingUser;
    }

    public function existsByUsername(string $username): bool
    {
        return $this->existingUser !== null && $this->existingUser->username() === mb_strtolower(trim($username));
    }

    public function save(User $user): User
    {
        $this->savedUser = $user;

        return $user;
    }
}

final class CreatePasswordHasher implements PasswordHasher
{
    public function hash(string $password): string
    {
        return 'hashed-'.$password;
    }

    public function verify(string $password, string $passwordHash): bool
    {
        return false;
    }
}
