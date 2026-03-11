<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_access_admin_booking_list(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($client)
            ->getJson('/admin/bookings')
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_booking_list(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)
            ->getJson('/admin/bookings')
            ->assertOk();
    }
}
