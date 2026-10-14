<?php

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookNotAvailable;
use App\Domain\Exceptions\BookRestockExceedsTotal;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function test_initializes_available_copies_to_total_copies(): void
    {
        $book = $this->book(totalCopies: 3);

        $this->assertSame(3, $book->totalCopies());
        $this->assertSame(3, $book->availableCopies());
    }

    public function test_trims_the_title_when_created(): void
    {
        $book = $this->book(title: '  Dune  ');

        $this->assertSame('Dune', $book->title());
    }

    public function test_rejects_an_empty_title(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A book title cannot be empty.');

        $this->book(title: '   ');
    }

    public function test_withdraw_decreases_available_copies(): void
    {
        $book = $this->book(totalCopies: 2);

        $book->withdraw();

        $this->assertSame(1, $book->availableCopies());
    }

    public function test_rejects_withdraw_when_no_copy_is_available(): void
    {
        $book = $this->book(totalCopies: 1);
        $book->withdraw();

        try {
            $book->withdraw();
            $this->fail('Expected a book with no available copies to reject withdrawal.');
        } catch (BookNotAvailable) {
            $this->assertSame(0, $book->availableCopies());
        }
    }

    public function test_restock_increases_available_copies_after_a_withdrawal(): void
    {
        $book = $this->book(totalCopies: 2);
        $book->withdraw();

        $book->restock();

        $this->assertSame(2, $book->availableCopies());
    }

    public function test_rejects_restock_when_available_copies_equal_total_copies(): void
    {
        $book = $this->book(totalCopies: 2);

        try {
            $book->restock();
            $this->fail('Expected restocking beyond total copies to be rejected.');
        } catch (BookRestockExceedsTotal) {
            $this->assertSame(2, $book->availableCopies());
        }
    }

    public function test_marks_a_book_as_deleted_without_removing_its_domain_state(): void
    {
        $book = $this->book();

        $book->markAsDeleted(new DateTimeImmutable('2026-10-14T10:00:00-05:00'));

        $this->assertFalse($book->isActive());
        $this->assertSame('2026-10-14T15:00:00+00:00', $book->deletedAt()?->format(DateTimeInterface::ATOM));
        $this->assertSame('Dune', $book->title());
        $this->assertSame('9780307474728', $book->isbn()->value());
    }

    private function book(string $title = 'Dune', int $totalCopies = 2): Book
    {
        return new Book(
            $title,
            new Isbn('9780307474728'),
            new Quantity($totalCopies),
        );
    }
}
