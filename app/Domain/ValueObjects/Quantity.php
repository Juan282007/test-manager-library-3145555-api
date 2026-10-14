<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Quantity
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('A quantity must be greater than zero.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
