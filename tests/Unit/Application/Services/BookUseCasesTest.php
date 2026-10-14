<?php

namespace Tests\Unit\Application\Services;

use App\Application\Contracts\BookRepository;
use App\Application\Contracts\CategoryRepository;
use App\Application\Contracts\CoverStorage;
use App\Application\Contracts\OpenLoanChecker;
use App\Application\Services\CreateBookService;
use App\Application\Services\DeleteBookService;
use App\Application\Services\RemoveBookCoverService;
use App\Application\Services\SearchBooksService;
use App\Application\Services\UpdateBookService;
use App\Application\Services\UploadBookCoverService;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookHasOpenLoans;
use App\Domain\Exceptions\InvalidCover;
use App\Domain\Exceptions\IsbnAlreadyTaken;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BookUseCasesTest extends TestCase
{
    public function test_creates_a_book_with_available_copies_equal_to_total(): void
    {
        $books = new FakeBookRepository;
        $service = new CreateBookService($books, new FakeCategoryRepository);

        $book = $service->create(' Dune ', 'Frank Herbert', '9780307474728', 'category-1', 3);

        $this->assertSame('Dune', $book->title());
        $this->assertSame('Frank Herbert', $book->author());
        $this->assertSame('category-1', $book->categoryId());
        $this->assertSame(3, $book->availableCopies());
    }

    public function test_rejects_an_isbn_already_used_by_an_active_book(): void
    {
        $books = new FakeBookRepository([
            new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Author', 'category-1', 'book-1'),
        ]);
        $service = new CreateBookService($books, new FakeCategoryRepository);

        $this->expectException(IsbnAlreadyTaken::class);

        $service->create('Another book', 'Author', '9780307474728', 'category-1', 1);
    }

    public function test_search_delegates_query_and_category_filter_to_the_repository(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1');
        $books = new FakeBookRepository([$book]);
        $service = new SearchBooksService($books);

        $result = $service->search('Dune', 'category-1');

        $this->assertSame([$book], $result);
        $this->assertSame(['Dune', 'category-1'], $books->lastSearch);
    }

    public function test_update_adjusts_available_copies_by_the_total_difference(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(3), 'Frank Herbert', 'category-1', 'book-1');
        $book->withdraw();
        $books = new FakeBookRepository([$book]);
        $service = new UpdateBookService($books, new FakeCategoryRepository);

        $updated = $service->update('book-1', 'Dune Messiah', 'Frank Herbert', 'category-1', 4);

        $this->assertSame('Dune Messiah', $updated->title());
        $this->assertSame(4, $updated->totalCopies());
        $this->assertSame(3, $updated->availableCopies());
    }

    public function test_delete_rejects_a_book_with_open_loans(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1');
        $service = new DeleteBookService(new FakeBookRepository([$book]), new FakeOpenLoanChecker(true));

        $this->expectException(BookHasOpenLoans::class);

        $service->delete('book-1', new DateTimeImmutable('2026-10-14T15:00:00Z'));
    }

    public function test_delete_marks_a_book_as_inactive(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1');
        $books = new FakeBookRepository([$book]);
        $service = new DeleteBookService($books, new FakeOpenLoanChecker(false));

        $deleted = $service->delete('book-1', new DateTimeImmutable('2026-10-14T15:00:00Z'));

        $this->assertFalse($deleted->isActive());
        $this->assertSame($deleted, $books->savedBook);
    }

    public function test_uploads_a_valid_cover_and_stores_only_its_key(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1');
        $books = new FakeBookRepository([$book]);
        $service = new UploadBookCoverService($books, new FakeCoverStorage);

        $uploaded = $service->upload('book-1', 'image/png', 1024, 'binary');

        $this->assertSame('covers/book-1.png', $uploaded->coverKey());
    }

    public function test_rejects_a_cover_larger_than_two_megabytes(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1');
        $service = new UploadBookCoverService(new FakeBookRepository([$book]), new FakeCoverStorage);

        $this->expectException(InvalidCover::class);

        $service->upload('book-1', 'image/png', 2 * 1024 * 1024 + 1, 'binary');
    }

    public function test_removes_the_cover_after_clearing_the_book_key(): void
    {
        $book = new Book('Dune', new Isbn('9780307474728'), new Quantity(1), 'Frank Herbert', 'category-1', 'book-1', 'covers/book-1.png');
        $books = new FakeBookRepository([$book]);
        $covers = new FakeCoverStorage;
        $service = new RemoveBookCoverService($books, $covers);

        $removed = $service->remove('book-1');

        $this->assertNull($removed->coverKey());
        $this->assertSame('covers/book-1.png', $covers->deletedKey);
    }
}

final class FakeBookRepository implements BookRepository
{
    /** @var list<Book> */
    private array $bookList;

    public ?Book $savedBook = null;

    /** @var array{0: ?string, 1: ?string}|null */
    public ?array $lastSearch = null;

    /** @param list<Book> $books */
    public function __construct(array $books = [])
    {
        $this->bookList = $books;
    }

    public function findById(string $id): ?Book
    {
        foreach ($this->bookList as $book) {
            if ($book->id() === $id) {
                return $book;
            }
        }

        return null;
    }

    public function existsActiveByIsbn(string $isbn, ?string $ignoreId = null): bool
    {
        foreach ($this->bookList as $book) {
            if ($book->isActive() && $book->isbn()->value() === $isbn && $book->id() !== $ignoreId) {
                return true;
            }
        }

        return false;
    }

    public function search(?string $query = null, ?string $categoryId = null): array
    {
        $this->lastSearch = [$query, $categoryId];

        return $this->bookList;
    }

    public function save(Book $book): Book
    {
        $this->savedBook = $book;

        return $book;
    }
}

final class FakeCategoryRepository implements CategoryRepository
{
    public function exists(string $id): bool
    {
        return $id === 'category-1';
    }
}

final class FakeOpenLoanChecker implements OpenLoanChecker
{
    public function __construct(private bool $hasLoans) {}

    public function hasOpenLoans(Book $book): bool
    {
        return $this->hasLoans;
    }
}

final class FakeCoverStorage implements CoverStorage
{
    public ?string $deletedKey = null;

    public function store(string $bookId, string $contentType, string $contents): string
    {
        return 'covers/'.$bookId.'.png';
    }

    public function delete(string $coverKey): void
    {
        $this->deletedKey = $coverKey;
    }
}
