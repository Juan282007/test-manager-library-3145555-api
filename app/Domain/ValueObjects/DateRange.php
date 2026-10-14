<?php

namespace App\Domain\ValueObjects;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final readonly class DateRange
{
    private DateTimeImmutable $from;

    private DateTimeImmutable $to;

    public function __construct(DateTimeInterface $from, DateTimeInterface $to)
    {
        $utc = new DateTimeZone('UTC');
        $this->from = DateTimeImmutable::createFromInterface($from)->setTimezone($utc);
        $this->to = DateTimeImmutable::createFromInterface($to)->setTimezone($utc);

        if ($this->to < $this->from) {
            throw new InvalidArgumentException('The end of a date range cannot be before its start.');
        }
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }
}
