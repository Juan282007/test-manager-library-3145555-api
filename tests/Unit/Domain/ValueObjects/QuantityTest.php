<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Quantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    public function test_returns_a_positive_quantity(): void
    {
        $quantity = new Quantity(3);

        $this->assertSame(3, $quantity->value());
    }

    public function test_rejects_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A quantity must be greater than zero.');

        new Quantity(0);
    }

    public function test_rejects_a_negative_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A quantity must be greater than zero.');

        new Quantity(-1);
    }
}
