<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Isbn
{
    public function __construct(private string $value)
    {
        if (preg_match('/^\d{13}$/D', $value) !== 1) {
            throw new InvalidArgumentException('An ISBN must contain exactly 13 digits.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
