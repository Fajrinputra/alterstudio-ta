<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_roles_merge_primary_and_additional_roles_uniquely(): void
    {
        $user = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
            'roles' => [Role::PHOTOGRAPHER->value, Role::EDITOR->value],
        ]);

        $this->assertSame([
            Role::PHOTOGRAPHER->value,
            Role::EDITOR->value,
        ], $user->effectiveRoles());
    }

    public function test_is_role_accepts_enum_and_string_values(): void
    {
        $user = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
            'roles' => [Role::EDITOR->value],
        ]);

        $this->assertTrue($user->isRole(Role::PHOTOGRAPHER));
        $this->assertTrue($user->isRole('EDITOR'));
        $this->assertFalse($user->isRole(Role::MANAGER));
        $this->assertTrue($user->hasBothCrewRoles());
    }

    public function test_with_role_scope_finds_primary_and_additional_roles(): void
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'roles' => null]);
        $dualCrew = User::factory()->create(['role' => Role::EDITOR, 'roles' => [Role::PHOTOGRAPHER->value]]);
        User::factory()->create(['role' => Role::CLIENT, 'roles' => null]);

        $ids = User::withRole(Role::PHOTOGRAPHER)->pluck('id')->all();

        $this->assertContains($photographer->id, $ids);
        $this->assertContains($dualCrew->id, $ids);
        $this->assertCount(2, $ids);
    }
}
