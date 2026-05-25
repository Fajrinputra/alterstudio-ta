<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleEnumTest extends TestCase
{
    /**
     * Pengujian: daftar role yang tersedia pada sistem.
     * Hasil yang diharapkan: enum Role mengembalikan seluruh role sesuai aktor sistem.
     */
    public function test_all_returns_every_role_value(): void
    {
        $this->assertSame([
            'OWNER',
            'ADMIN',
            'CLIENT',
            'PHOTOGRAPHER',
            'EDITOR',
            'MANAGER',
        ], Role::all());
    }
}
