<?php

namespace Tests\Unit\Application\Services;

use App\Application\Contracts\PasswordHasher;
use App\Application\Contracts\TokenIssuer;
use App\Application\Contracts\UserRepository;
use App\Application\Services\LoginService;
use App\Domain\Entities\User;
use App\Domain\Exceptions\InvalidCredentials;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase
{
    public function test_returns_a_bearer_token_for_valid_credentials(): void
    {
        $users = new LoginUserRepository(new User('ana', 'hashed-password', User::LIBRARIAN));
        $service = new LoginService($users, new LoginPasswordHasher, new LoginTokenIssuer);

        $result = $service->login('ana', 'secret');

        $this->assertSame('test-token', $result['access_token']);
        $this->assertSame('Bearer', $result['token_type']);
        $this->assertSame(28800, $result['expires_in']);
        $this->assertSame('librarian', $result['role']);
    }

    public function test_rejects_invalid_credentials_without_revealing_which_value_failed(): void
    {
        $users = new LoginUserRepository(new User('ana', 'hashed-password', User::LIBRARIAN));
        $service = new LoginService($users, new LoginPasswordHasher, new LoginTokenIssuer);

        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid username or password.');

        $service->login('ana', 'wrong');
    }
}

final class LoginUserRepository implements UserRepository
{
    public function __construct(private User $user) {}

    public function findByUsername(string $username): ?User
    {
        return $username === $this->user->username() ? $this->user : null;
    }

    public function existsByUsername(string $username): bool
    {
        return $this->findByUsername($username) !== null;
    }

    public function save(User $user): User
    {
        return $user;
    }
}

final class LoginPasswordHasher implements PasswordHasher
{
    public function hash(string $password): string
    {
        return 'hashed-'.$password;
    }

    public function verify(string $password, string $passwordHash): bool
    {
        return $password === 'secret' && $passwordHash === 'hashed-password';
    }
}

final class LoginTokenIssuer implements TokenIssuer
{
    public function issue(User $user): string
    {
        return 'test-token';
    }
}
