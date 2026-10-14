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

    private int $availableCopies;

    private ?DateTimeImmutable $deletedAt = null;

    public function __construct(
        string $title,
        private Isbn $isbn,
        private Quantity $totalCopies,
    ) {
        $normalizedTitle = trim($title);

        if ($normalizedTitle === '') {
            throw new InvalidArgumentException('A book title cannot be empty.');
        }

        $this->title = $normalizedTitle;
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
