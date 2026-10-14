<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Isbn;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IsbnTest extends TestCase
{
    public function test_returns_a_valid_13_digit_isbn(): void
    {
        $isbn = new Isbn('9780307474728');

        $this->assertSame('9780307474728', $isbn->value());
    }

    public function test_rejects_an_isbn_with_non_digit_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An ISBN must contain exactly 13 digits.');

        new Isbn('978030747472X');
    }

    public function test_rejects_an_isbn_with_an_invalid_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An ISBN must contain exactly 13 digits.');

        new Isbn('978030747472');
    }
}
