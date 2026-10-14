<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\OpenLoanChecker;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookHasOpenLoans;
use App\Domain\Exceptions\BookNotFound;
use DateTimeInterface;

final class DeleteBookService
{
    public function __construct(
        private BookRepository $books,
        private OpenLoanChecker $openLoans,
    ) {}

    public function delete(string $id, DateTimeInterface $deletedAt): Book
    {
        $book = $this->books->findById($id);

        if ($book === null || ! $book->isActive()) {
            throw new BookNotFound('The book does not exist.');
        }

        if ($this->openLoans->hasOpenLoans($book)) {
            throw new BookHasOpenLoans('The book has open loans.');
        }

        $book->markAsDeleted($deletedAt);

        return $this->books->save($book);
    }
}
