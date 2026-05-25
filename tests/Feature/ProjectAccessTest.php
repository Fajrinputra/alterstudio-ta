<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: akses detail project oleh kru.
     * Hasil yang diharapkan: kru yang ditugaskan dapat membuka detail, sedangkan kru lain ditolak.
     */
    public function test_assigned_crew_can_open_project_detail_and_unassigned_crew_is_forbidden(): void
    {
        [$project, $photographer] = $this->makeProject();
        $otherPhotographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);

        $this->actingAs($photographer)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Detail Pasca-Produksi', false);

        $this->actingAs($otherPhotographer)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    /**
     * Pengujian: akses monitoring detail project oleh admin dan manajer.
     * Hasil yang diharapkan: admin dan manajer dapat membuka detail project untuk pemantauan.
     */
    public function test_admin_and_manager_can_open_project_detail_for_monitoring(): void
    {
        [$project] = $this->makeProject();
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($admin)->get(route('projects.show', $project))->assertOk();
        $this->actingAs($manager)->get(route('projects.show', $project))->assertOk();
    }

    /**
     * Pengujian: akses klien ke link Drive foto mentah.
     * Hasil yang diharapkan: klien pemilik diarahkan ke Drive, sedangkan klien lain ditolak.
     */
    public function test_client_raw_download_redirects_to_drive_only_for_owned_project(): void
    {
        [$project, , , $client] = $this->makeProject([
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-access',
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($client)
            ->get(route('projects.raw.download', $project))
            ->assertRedirect('https://drive.google.com/drive/folders/raw-access');

        $this->actingAs($otherClient)
            ->get(route('projects.raw.download', $project))
            ->assertForbidden();
    }

    /**
     * @param array<string, mixed> $projectAttributes
     * @return array{0: Project, 1: User, 2: User, 3: User}
     */
    private function makeProject(array $projectAttributes = []): array
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Detail Project',
            'slug' => 'cabang-detail-project',
            'address' => 'Jl. Detail Project',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $project = Project::factory()->create(array_merge([
            'booking_id' => $booking->id,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'status' => Project::STATUS_SCHEDULED,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
        ], $projectAttributes));

        return [$project, $photographer, $editor, $client];
    }
}
