<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\CoverStorage;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookNotFound;

final class RemoveBookCoverService
{
    public function __construct(
        private BookRepository $books,
        private CoverStorage $covers,
    ) {}

    public function remove(string $id): Book
    {
        $book = $this->books->findById($id);

        if ($book === null || ! $book->isActive()) {
            throw new BookNotFound('The book does not exist.');
        }

        $coverKey = $book->coverKey();
        $book->setCoverKey(null);
        $savedBook = $this->books->save($book);

        if ($coverKey !== null) {
            $this->covers->delete($coverKey);
        }

        return $savedBook;
    }
}
