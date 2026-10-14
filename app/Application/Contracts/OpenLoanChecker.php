<?php

namespace App\Application\Contracts;

use App\Domain\Entities\Book;

interface OpenLoanChecker
{
    public function hasOpenLoans(Book $book): bool;
}
