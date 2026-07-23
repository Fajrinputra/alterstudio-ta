<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementSafetyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: admin memperbarui jadwal project yang belum berjalan.
     * Hasil yang diharapkan: fotografer dan editor baru tersimpan pada project.
     */
    public function test_admin_can_update_schedule_when_project_not_started(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographerA = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $photographerB = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editorA = User::factory()->create(['role' => Role::EDITOR]);
        $editorB = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio A', 'is_active' => true]);

        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'status' => 'PAID',
            'package_id' => $package->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'SCHEDULED',
            'selections_locked' => false,
        ]);

        $project->update([
            'photographer_id' => $photographerA->id,
            'editor_id' => $editorA->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        $this->actingAs($admin)
            ->putJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographerB->id,
                'editor_id' => $editorB->id,
                'studio_room_code' => $room->room_code,
            ])
            ->assertOk();

        $this->assertDatabaseHas('project_schedules', [
            'project_id' => $project->id,
            'photographer_id' => $photographerB->id,
            'editor_id' => $editorB->id,
        ]);
    }

    /**
     * Pengujian: endpoint hapus jadwal project.
     * Hasil yang diharapkan: penghapusan jadwal tidak tersedia dan data penugasan tetap tersimpan.
     */
    /**
     * Pengujian: jadwal tidak dapat dihapus ketika project sudah di tahap EDITING.
     * Hasil yang diharapkan: endpoint menolak permintaan hapus (422) dan data jadwal tetap ada.
     */
    public function test_cannot_delete_schedule_when_project_is_in_editing_stage(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio A', 'is_active' => true]);

        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'status' => 'PAID',
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'EDITING',
            'selections_locked' => true,
        ]);

        $project->update([
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        // Endpoint ada, tapi harus menolak karena project sudah di tahap EDITING
        $this->actingAs($admin)
            ->deleteJson("/projects/{$project->id}/schedule")
            ->assertStatus(422);

        // Data jadwal tetap tidak terhapus
        $this->assertDatabaseHas('project_schedules', [
            'project_id' => $project->id,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
        ]);
    }
}
