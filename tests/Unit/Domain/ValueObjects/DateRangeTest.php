<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\DateRange;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function test_normalizes_range_boundaries_to_utc(): void
    {
        $range = new DateRange(
            new DateTimeImmutable('2026-10-01T10:00:00-05:00'),
            new DateTimeImmutable('2026-10-01T11:00:00-05:00'),
        );

        $this->assertSame('2026-10-01T15:00:00+00:00', $range->from()->format(DateTimeInterface::ATOM));
        $this->assertSame('2026-10-01T16:00:00+00:00', $range->to()->format(DateTimeInterface::ATOM));
    }

    public function test_allows_a_range_with_equal_start_and_end(): void
    {
        $instant = new DateTimeImmutable('2026-10-01T00:00:00Z');
        $range = new DateRange($instant, $instant);

        $this->assertSame($range->from()->format(DateTimeInterface::ATOM), $range->to()->format(DateTimeInterface::ATOM));
    }

    public function test_rejects_a_range_with_an_end_before_its_start(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The end of a date range cannot be before its start.');

        new DateRange(
            new DateTimeImmutable('2026-10-02T00:00:00Z'),
            new DateTimeImmutable('2026-10-01T00:00:00Z'),
        );
    }
}
