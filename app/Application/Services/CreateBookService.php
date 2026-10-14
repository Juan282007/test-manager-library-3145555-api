<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\CategoryRepository;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\CategoryNotFound;
use App\Domain\Exceptions\IsbnAlreadyTaken;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;

final class CreateBookService
{
    public function __construct(
        private BookRepository $books,
        private CategoryRepository $categories,
    ) {}

    public function create(string $title, string $author, string $isbn, string $categoryId, int $totalCopies): Book
    {
        if (! $this->categories->exists($categoryId)) {
            throw new CategoryNotFound('The category does not exist.');
        }

        $isbnValue = new Isbn($isbn);

        if ($this->books->existsActiveByIsbn($isbnValue->value())) {
            throw new IsbnAlreadyTaken('The ISBN is already assigned to an active book.');
        }

        return $this->books->save(new Book(
            $title,
            $isbnValue,
            new Quantity($totalCopies),
            $author,
            $categoryId,
        ));
    }
}
