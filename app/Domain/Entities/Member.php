<?php

namespace App\Domain\Entities;

use InvalidArgumentException;

final class Member
{
    private string $fullName;

    private string $documentNumber;

    public function __construct(string $fullName, string $documentNumber)
    {
        $normalizedName = trim($fullName);
        $normalizedDocument = trim($documentNumber);

        if ($normalizedName === '') {
            throw new InvalidArgumentException('A member name cannot be empty.');
        }

        if ($normalizedDocument === '') {
            throw new InvalidArgumentException('A member document number cannot be empty.');
        }

        $this->fullName = $normalizedName;
        $this->documentNumber = $normalizedDocument;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function documentNumber(): string
    {
        return $this->documentNumber;
    }
}
