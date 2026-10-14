<?php

namespace App\Application\Services;

use App\Application\Contracts\PasswordHasher;
use App\Application\Contracts\TokenIssuer;
use App\Application\Contracts\UserRepository;
use App\Domain\Exceptions\InvalidCredentials;

final class LoginService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private TokenIssuer $tokenIssuer,
    ) {}

    /** @return array{access_token: string, token_type: string, expires_in: int, role: string} */
    public function login(string $username, string $password): array
    {
        $user = $this->users->findByUsername($username);

        if ($user === null || ! $this->passwordHasher->verify($password, $user->passwordHash())) {
            throw new InvalidCredentials('Invalid username or password.');
        }

        return [
            'access_token' => $this->tokenIssuer->issue($user),
            'token_type' => 'Bearer',
            'expires_in' => 8 * 60 * 60,
            'role' => $user->role(),
        ];
    }
}
