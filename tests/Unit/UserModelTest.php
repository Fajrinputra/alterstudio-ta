<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: pengecekan role pengguna menggunakan enum dan string.
     * Hasil yang diharapkan: sistem hanya membaca role utama pengguna.
     */
    public function test_is_role_accepts_enum_and_string_values(): void
    {
        $user = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
        ]);

        $this->assertTrue($user->isRole(Role::PHOTOGRAPHER));
        $this->assertTrue($user->isRole('PHOTOGRAPHER'));
        $this->assertFalse($user->isRole('EDITOR'));
        $this->assertFalse($user->isRole(Role::EDITOR));
        $this->assertFalse($user->isRole(Role::MANAGER));
    }

    public function test_is_role_accepts_multiple_allowed_roles(): void
    {
        $user = User::factory()->create(['role' => Role::EDITOR]);

        $this->assertTrue($user->isRole(Role::PHOTOGRAPHER, Role::EDITOR));
        $this->assertTrue($user->isRole('EDITOR'));
        $this->assertFalse($user->isRole(Role::OWNER, Role::MANAGER));
        $this->assertFalse($user->isRole(Role::CLIENT));
    }

    /**
     * Pengujian: scope pencarian pengguna berdasarkan role.
     * Hasil yang diharapkan: pengguna ditemukan berdasarkan role utama.
     */
    public function test_with_role_scope_finds_primary_role(): void
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        User::factory()->create(['role' => Role::EDITOR]);
        User::factory()->create(['role' => Role::CLIENT]);

        $ids = User::withRole(Role::PHOTOGRAPHER)->pluck('id')->all();

        $this->assertContains($photographer->id, $ids);
        $this->assertCount(1, $ids);
    }
}
