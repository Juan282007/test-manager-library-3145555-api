<?php

namespace App\Domain\Entities;

use App\Domain\Exceptions\ItemAlreadyReturned;
use App\Domain\ValueObjects\Isbn;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class LoanItem
{
    private string $categoryName;

    private DateTimeImmutable $loanedAt;

    private DateTimeImmutable $dueAt;

    private ?DateTimeImmutable $returnedAt = null;

    public function __construct(
        private Book $book,
        string $categoryName,
        DateTimeInterface $loanedAt,
    ) {
        $this->categoryName = trim($categoryName);
        $this->loanedAt = self::toUtc($loanedAt);
        $this->dueAt = $this->loanedAt->add(new DateInterval('P14D'));
    }

    public function book(): Book
    {
        return $this->book;
    }

    public function title(): string
    {
        return $this->book->title();
    }

    public function isbn(): Isbn
    {
        return $this->book->isbn();
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function loanedAt(): DateTimeImmutable
    {
        return $this->loanedAt;
    }

    public function dueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function returnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function returnAt(DateTimeInterface $returnedAt): void
    {
        if ($this->returnedAt !== null) {
            throw new ItemAlreadyReturned('This loan item has already been returned.');
        }

        $this->book->restock();
        $this->returnedAt = self::toUtc($returnedAt);
    }

    private static function toUtc(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new DateTimeZone('UTC'));
    }
}
