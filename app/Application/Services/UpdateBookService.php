<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\CategoryRepository;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookNotFound;
use App\Domain\Exceptions\CategoryNotFound;
use App\Domain\ValueObjects\Quantity;

final class UpdateBookService
{
    public function __construct(
        private BookRepository $books,
        private CategoryRepository $categories,
    ) {}

    public function update(string $id, string $title, string $author, string $categoryId, int $totalCopies): Book
    {
        $book = $this->books->findById($id);

        if ($book === null || ! $book->isActive()) {
            throw new BookNotFound('The book does not exist.');
        }

        if (! $this->categories->exists($categoryId)) {
            throw new CategoryNotFound('The category does not exist.');
        }

        $book->updateDetails($title, $author, $categoryId);
        $book->changeTotalCopies(new Quantity($totalCopies));

        return $this->books->save($book);
    }
}
