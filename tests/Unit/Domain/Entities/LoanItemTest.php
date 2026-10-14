<?php

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\Book;
use App\Domain\Entities\LoanItem;
use App\Domain\Exceptions\ItemAlreadyReturned;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class LoanItemTest extends TestCase
{
    public function test_return_restocks_the_book_and_records_return_time_in_utc(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1));
        $book->withdraw();
        $item = new LoanItem($book, 'Novela', new DateTimeImmutable('2026-10-14T15:00:00Z'));

        $item->returnAt(new DateTimeImmutable('2026-10-16T10:00:00-05:00'));

        $this->assertSame(1, $book->availableCopies());
        $this->assertSame('2026-10-16T15:00:00+00:00', $item->returnedAt()?->format(DateTimeInterface::ATOM));
    }

    public function test_rejects_returning_an_item_twice_without_restocking_again(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1));
        $book->withdraw();
        $item = new LoanItem($book, 'Novela', new DateTimeImmutable('2026-10-14T15:00:00Z'));
        $item->returnAt(new DateTimeImmutable('2026-10-16T15:00:00Z'));

        $this->expectException(ItemAlreadyReturned::class);
        $item->returnAt(new DateTimeImmutable('2026-10-17T15:00:00Z'));
    }
}
