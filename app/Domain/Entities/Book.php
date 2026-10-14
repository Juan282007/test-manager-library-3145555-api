<?php

namespace App\Domain\Entities;

use App\Domain\Exceptions\BookNotAvailable;
use App\Domain\Exceptions\BookRestockExceedsTotal;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final class Book
{
    private string $title;

    private string $author;

    private string $categoryId;

    private ?string $id;

    private ?string $coverKey = null;

    private int $availableCopies;

    private ?DateTimeImmutable $deletedAt = null;

    public function __construct(
        string $title,
        private Isbn $isbn,
        private Quantity $totalCopies,
        string $author = '',
        string $categoryId = '',
        ?string $id = null,
        ?string $coverKey = null,
    ) {
        $normalizedTitle = trim($title);

        if ($normalizedTitle === '') {
            throw new InvalidArgumentException('A book title cannot be empty.');
        }

        $this->title = $normalizedTitle;
        $this->author = trim($author);
        $this->categoryId = trim($categoryId);
        $this->id = $id;
        $this->setCoverKey($coverKey);
        $this->availableCopies = $totalCopies->value();
    }

    public function title(): string
    {
        return $this->title;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function coverKey(): ?string
    {
        return $this->coverKey;
    }

    public function updateDetails(string $title, string $author, string $categoryId): void
    {
        $normalizedTitle = trim($title);

        if ($normalizedTitle === '') {
            throw new InvalidArgumentException('A book title cannot be empty.');
        }

        $this->title = $normalizedTitle;
        $this->author = trim($author);
        $this->categoryId = trim($categoryId);
    }

    public function changeIsbn(Isbn $isbn): void
    {
        $this->isbn = $isbn;
    }

    public function changeTotalCopies(Quantity $totalCopies): void
    {
        $difference = $totalCopies->value() - $this->totalCopies();
        $newAvailableCopies = $this->availableCopies + $difference;

        if ($newAvailableCopies < 0) {
            throw new InvalidArgumentException('Available copies cannot become negative.');
        }

        $this->totalCopies = $totalCopies;
        $this->availableCopies = $newAvailableCopies;
    }

    public function setCoverKey(?string $coverKey): void
    {
        $normalizedCoverKey = $coverKey !== null ? trim($coverKey) : null;

        if ($normalizedCoverKey === '') {
            throw new InvalidArgumentException('A cover key cannot be empty.');
        }

        $this->coverKey = $normalizedCoverKey;
    }

    public function totalCopies(): int
    {
        return $this->totalCopies->value();
    }

    public function availableCopies(): int
    {
        return $this->availableCopies;
    }

    public function withdraw(): void
    {
        if ($this->availableCopies === 0) {
            throw new BookNotAvailable('This book has no available copies.');
        }

        $this->availableCopies--;
    }

    public function restock(): void
    {
        if ($this->availableCopies === $this->totalCopies()) {
            throw new BookRestockExceedsTotal('Available copies cannot exceed total copies.');
        }

        $this->availableCopies++;
    }

    public function markAsDeleted(DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = DateTimeImmutable::createFromInterface($deletedAt)
            ->setTimezone(new DateTimeZone('UTC'));
    }

    public function isActive(): bool
    {
        return $this->deletedAt === null;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
