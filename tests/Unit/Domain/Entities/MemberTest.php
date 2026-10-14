<?php

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\Member;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MemberTest extends TestCase
{
    public function test_trims_member_name_and_document_number(): void
    {
        $member = new Member('  Ana Gómez  ', '  CC-123  ');

        $this->assertSame('Ana Gómez', $member->fullName());
        $this->assertSame('CC-123', $member->documentNumber());
    }

    public function test_rejects_an_empty_member_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A member name cannot be empty.');

        new Member('   ', 'CC-123');
    }

    public function test_rejects_an_empty_document_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A member document number cannot be empty.');

        new Member('Ana Gómez', '   ');
    }
}
