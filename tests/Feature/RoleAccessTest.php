<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: pembatasan akses klien ke daftar booking admin.
     * Hasil yang diharapkan: klien ditolak karena tidak memiliki hak akses operasional admin.
     */
    public function test_client_cannot_access_admin_booking_list(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($client)
            ->getJson('/admin/bookings')
            ->assertStatus(403);
    }

    /**
     * Pengujian: akses Admin ke daftar booking operasional.
     * Hasil yang diharapkan: admin dapat membuka daftar booking untuk pengelolaan pemesanan.
     */
    public function test_admin_can_access_admin_booking_list(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)
            ->getJson('/admin/bookings')
            ->assertOk();
    }

    /**
     * Pengujian: pembatasan akses Manajer ke halaman manajemen jadwal.
     * Hasil yang diharapkan: Manajer ditolak karena penjadwalan merupakan kewenangan Admin.
     */
    public function test_manager_cannot_access_schedule_management_page(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($manager)
            ->get('/admin/schedules')
            ->assertStatus(403);
    }

    /**
     * Pengujian: pembatasan Manajer saat menyimpan jadwal project.
     * Hasil yang diharapkan: request penyimpanan jadwal oleh Manajer ditolak.
     */
    public function test_manager_cannot_store_project_schedule(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $project = Project::factory()->create();

        $this->actingAs($manager)
            ->postJson("/projects/{$project->id}/schedule", [])
            ->assertStatus(403);
    }

    /**
     * Pengujian: pembatasan Manajer terhadap master data Owner.
     * Hasil yang diharapkan: Manajer tidak dapat mengakses pengelolaan pengguna dan cabang.
     */
    public function test_manager_cannot_access_owner_master_data_pages(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($manager)
            ->get(route('admin.users.index'))
            ->assertStatus(403);

        $this->actingAs($manager)
            ->get(route('admin.locations.manage'))
            ->assertStatus(403);
    }

    /**
     * Pengujian: akses Owner ke menu pengguna, cabang, dan laporan.
     * Hasil yang diharapkan: Owner dapat membuka halaman sesuai peran super admin.
     */
    public function test_owner_can_access_user_location_and_report_pages(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $this->actingAs($owner)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('admin.locations.manage'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('reports.index'))
            ->assertOk();
    }
}
