<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Domain\Entities\Book;

final class SearchBooksService
{
    public function __construct(private BookRepository $books) {}

    /** @return list<Book> */
    public function search(?string $query = null, ?string $categoryId = null): array
    {
        return $this->books->search($query, $categoryId);
    }
}
