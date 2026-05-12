<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleEnumTest extends TestCase
{
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
