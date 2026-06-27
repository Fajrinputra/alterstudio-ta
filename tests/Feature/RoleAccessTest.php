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

    /** Manajer tidak memiliki akses ke halaman penjadwalan kru. */
    public function test_manager_cannot_access_schedule_management_page(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($manager)
            ->get('/admin/schedules')
            ->assertForbidden();
    }

    /** Manajer tidak dapat menyimpan jadwal project. */
    public function test_manager_cannot_store_project_schedule(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $project = Project::factory()->create();
        $this->actingAs($manager)
            ->postJson("/projects/{$project->id}/schedule", [])
            ->assertForbidden();
    }

    /**
     * Pengujian: pemindahan akses kelola hero landing dari Admin ke Manajer.
     * Hasil yang diharapkan: Admin ditolak dan Manajer dapat membuka halaman kelola hero landing.
     */
    public function test_landing_hero_management_is_only_available_for_manager(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($admin)
            ->get(route('manager.landing.hero'))
            ->assertStatus(403);

        $this->actingAs($manager)
            ->get(route('manager.landing.hero'))
            ->assertOk()
            ->assertViewIs('admin.landing.hero');
    }

    /**
     * Pengujian: kelola katalog, kategori, dan paket hanya untuk Admin dan Manajer.
     * Hasil yang diharapkan: Owner, Klien, Fotografer, dan Editor ditolak dari menu kelola katalog.
     */
    public function test_catalog_management_is_only_available_for_admin_and_manager(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($admin)
            ->get(route('admin.catalog'))
            ->assertOk();

        $this->actingAs($manager)
            ->get(route('admin.catalog'))
            ->assertOk();

        foreach ([Role::OWNER, Role::CLIENT, Role::PHOTOGRAPHER, Role::EDITOR] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('admin.catalog'))
                ->assertStatus(403);
        }
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
