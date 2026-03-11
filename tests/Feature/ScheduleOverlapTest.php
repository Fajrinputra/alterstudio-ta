<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
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

        $package = ServicePackage::factory()->create();

        $bookingA = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id]);
        $bookingB = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id]);

        $projectA = Project::factory()->create(['booking_id' => $bookingA->id]);
        $projectB = Project::factory()->create(['booking_id' => $bookingB->id]);

        $this->actingAs($admin)
            ->postJson("/projects/{$projectA->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'start_at' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
                'end_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
                'location' => 'Studio 1',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson("/projects/{$projectB->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'start_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(), // overlap
                'end_at' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                'location' => 'Studio 2',
            ])
            ->assertStatus(422);
    }
}
