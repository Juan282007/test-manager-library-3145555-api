<?php

namespace App\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\CoverStorage;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookNotFound;
use App\Domain\Exceptions\InvalidCover;

final class UploadBookCoverService
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    public function __construct(
        private BookRepository $books,
        private CoverStorage $covers,
    ) {}

    public function upload(string $id, string $contentType, int $size, string $contents): Book
    {
        $book = $this->books->findById($id);

        if ($book === null || ! $book->isActive()) {
            throw new BookNotFound('The book does not exist.');
        }

        if (! in_array($contentType, ['image/jpeg', 'image/png'], true) || $size > self::MAX_BYTES || $size < 1) {
            throw new InvalidCover('Cover must be a JPG or PNG up to 2 MB.');
        }

        $coverKey = $this->covers->store($id, $contentType, $contents);
        $book->setCoverKey($coverKey);

        return $this->books->save($book);
    }
}
