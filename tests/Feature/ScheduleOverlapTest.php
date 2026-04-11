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

class ScheduleOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlap_for_same_photographer_is_blocked(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $roomA = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio 1', 'is_active' => true]);
        $roomB = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio 2', 'is_active' => true]);

        $package = ServicePackage::factory()->create();

        $bookingA = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id, 'studio_location_id' => $location->id, 'studio_room_id' => $roomA->id]);
        $bookingB = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id, 'studio_location_id' => $location->id, 'studio_room_id' => $roomB->id]);

        $projectA = Project::factory()->create(['booking_id' => $bookingA->id]);
        $projectB = Project::factory()->create(['booking_id' => $bookingB->id]);

        $this->actingAs($admin)
            ->postJson("/projects/{$projectA->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $roomA->id,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson("/projects/{$projectB->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $roomB->id,
            ])
            ->assertStatus(422);
    }
}
