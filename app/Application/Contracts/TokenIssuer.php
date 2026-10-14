<?php

namespace App\Application\Contracts;

use App\Domain\Entities\User;

interface TokenIssuer
{
    public function issue(User $user): string;
}
