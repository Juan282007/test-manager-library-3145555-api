<?php

namespace App\Domain\Entities;

use App\Domain\Exceptions\DuplicateBookInLoan;
use App\Domain\Exceptions\LoanLimitExceeded;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final class Loan
{
    private const MAX_ITEMS = 3;

    /** @var list<LoanItem> */
    private array $items = [];

    private DateTimeImmutable $loanedAt;

    public function __construct(DateTimeInterface $loanedAt)
    {
        $this->loanedAt = DateTimeImmutable::createFromInterface($loanedAt)
            ->setTimezone(new DateTimeZone('UTC'));
    }

    public function addItem(Book $book, string $categoryName): void
    {
        if (count($this->items) >= self::MAX_ITEMS) {
            throw new LoanLimitExceeded('A loan cannot contain more than 3 items.');
        }

        foreach ($this->items as $item) {
            if ($item->isbn()->value() === $book->isbn()->value()) {
                throw new DuplicateBookInLoan('A loan cannot contain the same book twice.');
            }
        }

        $book->withdraw();
        $this->items[] = new LoanItem($book, $categoryName, $this->loanedAt);
    }

    public function returnItem(string $isbn, DateTimeInterface $returnedAt): void
    {
        foreach ($this->items as $item) {
            if ($item->isbn()->value() === $isbn) {
                $item->returnAt($returnedAt);

                return;
            }
        }

        throw new InvalidArgumentException('The book is not part of this loan.');
    }

    /** @return list<LoanItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function loanedAt(): DateTimeImmutable
    {
        return $this->loanedAt;
    }

    public function status(DateTimeInterface $now): string
    {
        if ($this->items !== [] && count(array_filter($this->items, static fn (LoanItem $item): bool => ! $item->isReturned())) === 0) {
            return 'closed';
        }

        $currentTime = $this->toUtc($now);

        foreach ($this->items as $item) {
            if (! $item->isReturned() && $item->dueAt() < $currentTime) {
                return 'overdue';
            }
        }

        return 'open';
    }

    private function toUtc(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new DateTimeZone('UTC'));
    }
}
