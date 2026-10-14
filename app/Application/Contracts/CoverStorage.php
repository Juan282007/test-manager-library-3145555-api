<?php

namespace App\Application\Contracts;

interface CoverStorage
{
    public function store(string $bookId, string $contentType, string $contents): string;

    public function delete(string $coverKey): void;
}
