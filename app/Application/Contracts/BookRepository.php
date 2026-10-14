<?php

namespace App\Application\Contracts;

use App\Domain\Entities\Book;

interface BookRepository
{
    public function findById(string $id): ?Book;

    public function existsActiveByIsbn(string $isbn, ?string $ignoreId = null): bool;

    /** @return list<Book> */
    public function search(?string $query = null, ?string $categoryId = null): array;

    public function save(Book $book): Book;
}
