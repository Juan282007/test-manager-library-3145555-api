<?php

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\Book;
use App\Domain\Entities\Loan;
use App\Domain\Exceptions\DuplicateBookInLoan;
use App\Domain\Exceptions\LoanLimitExceeded;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class LoanTest extends TestCase
{
    public function test_add_item_withdraws_one_copy_and_freezes_due_date(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $book = $this->book('9780307474728', 'Dune');

        $loan = new Loan($clock->now());
        $loan->addItem($book, ' Novela ');

        $item = $loan->items()[0];

        $this->assertSame(0, $book->availableCopies());
        $this->assertSame('Novela', $item->categoryName());
        $this->assertSame('2026-10-28T15:00:00+00:00', $item->dueAt()->format(DateTimeInterface::ATOM));
        $this->assertSame('Dune', $item->title());
    }

    public function test_calculates_open_status_before_due_date(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $loan = $this->loanWithOneBook($clock);

        $this->assertSame('open', $loan->status($clock->now()->modify('+13 days')));
    }

    public function test_calculates_overdue_status_after_due_date(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $loan = $this->loanWithOneBook($clock);

        $this->assertSame('overdue', $loan->status($clock->now()->modify('+15 days')));
    }

    public function test_calculates_closed_status_after_all_items_are_returned(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $loan = $this->loanWithOneBook($clock);
        $loan->returnItem('9780307474728', $clock->now()->modify('+2 days'));

        $this->assertSame('closed', $loan->status($clock->now()->modify('+15 days')));
    }

    public function test_rejects_a_duplicate_book_without_withdrawing_again(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $book = $this->book('9780307474728', 'Dune');
        $loan = new Loan($clock->now());
        $loan->addItem($book, 'Novela');

        $this->expectException(DuplicateBookInLoan::class);
        $loan->addItem($book, 'Novela');
    }

    public function test_rejects_a_fourth_item_and_keeps_it_available(): void
    {
        $clock = new FrozenClock('2026-10-14T15:00:00Z');
        $loan = new Loan($clock->now());

        foreach ([
            '9780307474728',
            '9780140328721',
            '9780743273565',
        ] as $isbn) {
            $loan->addItem($this->book($isbn, 'Book'), 'Novela');
        }

        $fourthBook = $this->book('9780451524935', 'Book');

        try {
            $loan->addItem($fourthBook, 'Novela');
            $this->fail('Expected a loan to reject a fourth item.');
        } catch (LoanLimitExceeded) {
            $this->assertSame(3, count($loan->items()));
            $this->assertSame(1, $fourthBook->availableCopies());
        }
    }

    private function loanWithOneBook(FrozenClock $clock): Loan
    {
        $loan = new Loan($clock->now());
        $loan->addItem($this->book('9780307474728', 'Dune'), 'Novela');

        return $loan;
    }

    private function book(string $isbn, string $title): Book
    {
        return new Book($title, new Isbn($isbn), new Quantity(1));
    }
}

final class FrozenClock
{
    private DateTimeImmutable $currentTime;

    public function __construct(string $currentTime)
    {
        $this->currentTime = new DateTimeImmutable($currentTime);
    }

    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }
}
