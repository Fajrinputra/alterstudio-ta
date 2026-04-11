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

    public function test_admin_can_update_schedule_when_project_not_started(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographerA = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $photographerB = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editorA = User::factory()->create(['role' => Role::EDITOR]);
        $editorB = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio A', 'is_active' => true]);

        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'status' => 'PAID',
            'package_id' => $package->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
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
                'studio_room_id' => $room->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'photographer_id' => $photographerB->id,
            'editor_id' => $editorB->id,
        ]);
    }

    public function test_admin_cannot_delete_schedule_when_project_already_running(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio A', 'is_active' => true]);

        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'status' => 'PAID',
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'EDITING',
        ]);

        $project->update([
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        $this->actingAs($admin)
            ->deleteJson("/projects/{$project->id}/schedule")
            ->assertStatus(422);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'photographer_id' => $photographer->id,
        ]);
    }
}
