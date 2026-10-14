<?php

namespace App\Application\Contracts;

interface CategoryRepository
{
    public function exists(string $id): bool;
}
