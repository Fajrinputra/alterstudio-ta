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

    /**
     * Pengujian: bentrok jadwal pada fotografer yang sama.
     * Hasil yang diharapkan: sistem menolak jadwal kedua jika fotografer sudah bertugas pada waktu tersebut.
     */
    public function test_overlap_for_same_photographer_is_blocked(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang 1', 'slug' => 'cabang-1', 'is_active' => true]);
        $roomA = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio 1', 'is_active' => true]);
        $roomB = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio 2', 'is_active' => true]);

        $package = ServicePackage::factory()->create();

        $bookingA = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id, 'studio_location_code' => $location->location_code, 'studio_room_code' => $roomA->room_code]);
        $bookingB = Booking::factory()->create(['status' => 'PAID', 'package_id' => $package->id, 'studio_location_code' => $location->location_code, 'studio_room_code' => $roomB->room_code]);

        $projectA = Project::factory()->create(['booking_id' => $bookingA->id]);
        $projectB = Project::factory()->create(['booking_id' => $bookingB->id]);

        $this->actingAs($admin)
            ->postJson("/projects/{$projectA->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_code' => $roomA->room_code,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson("/projects/{$projectB->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_code' => $roomB->room_code,
            ])
            ->assertStatus(422);
    }

    /**
     * Pengujian: bentrok jadwal pada ruangan studio yang sama.
     * Hasil yang diharapkan: sistem menolak penggunaan ruangan yang sama pada jam yang berbenturan.
     */
    public function test_overlap_for_same_studio_room_is_blocked_even_with_different_crew(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographerA = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $photographerB = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editorA = User::factory()->create(['role' => Role::EDITOR]);
        $editorB = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create(['name' => 'Cabang Room Overlap', 'slug' => 'cabang-room-overlap', 'is_active' => true]);
        $roomA = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio A', 'is_active' => true]);
        $roomB = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio B', 'is_active' => true]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $bookingDate = now()->addDay()->toDateString();

        $bookingA = Booking::factory()->create([
            'status' => Booking::STATUS_PAID,
            'package_id' => $package->id,
            'booking_date' => $bookingDate,
            'booking_time' => '13:00',
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $roomA->room_code,
        ]);
        $bookingB = Booking::factory()->create([
            'status' => Booking::STATUS_PAID,
            'package_id' => $package->id,
            'booking_date' => $bookingDate,
            'booking_time' => '13:30',
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $roomB->room_code,
        ]);

        $projectA = Project::factory()->create(['booking_id' => $bookingA->id]);
        $projectB = Project::factory()->create(['booking_id' => $bookingB->id]);

        $this->actingAs($admin)
            ->postJson("/projects/{$projectA->id}/schedule", [
                'photographer_id' => $photographerA->id,
                'editor_id' => $editorA->id,
                'studio_room_code' => $roomA->room_code,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson("/projects/{$projectB->id}/schedule", [
                'photographer_id' => $photographerB->id,
                'editor_id' => $editorB->id,
                'studio_room_code' => $roomA->room_code,
            ])
            ->assertStatus(422);
    }

    /**
     * Pengujian: bentrok ruangan juga terdeteksi dari booking aktif yang belum memiliki schedule.
     * Hasil yang diharapkan: sistem menolak jadwal jika ada booking lain pada ruangan dan jam yang sama.
     */
    public function test_room_overlap_is_blocked_from_existing_booking_without_schedule(): void
    {
        config()->set('studio.booking_buffer_minutes', 15);

        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $location = StudioLocation::create([
            'name' => 'Cabang Booking Overlap',
            'slug' => 'cabang-booking-overlap',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_code' => $location->location_code,
            'name' => 'Studio Booking Overlap',
            'is_active' => true,
        ]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $bookingDate = now()->addDay()->toDateString();

        Booking::factory()->create([
            'status' => Booking::STATUS_PAID,
            'package_id' => $package->id,
            'booking_date' => $bookingDate,
            'booking_time' => '11:00',
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);
        $bookingToSchedule = Booking::factory()->create([
            'status' => Booking::STATUS_PAID,
            'package_id' => $package->id,
            'booking_date' => $bookingDate,
            'booking_time' => '11:30',
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);
        $project = Project::factory()->create(['booking_id' => $bookingToSchedule->id]);

        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_code' => $room->room_code,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.');
    }
}
