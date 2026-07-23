<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCodeSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_code_columns_are_generated_and_synced(): void
    {
        $location = StudioLocation::create([
            'name' => 'Cabang Kode',
            'slug' => 'cabang-kode',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_code' => $location->location_code,
            'name' => 'Studio Kode',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);
        $project = Project::create([
            'booking_id' => $booking->id,
            'status' => 'DRAFT',
        ]);
        $schedule = ProjectSchedule::create([
            'project_id' => $project->id,
            'booking_id' => $booking->id,
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
            'scheduled_by' => $admin->id,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'status' => ProjectSchedule::STATUS_SCHEDULED,
        ]);
        $slide = LandingHeroSlide::create([
            'user_id' => $admin->id,
            'title' => 'Hero Kode',
            'image_path' => 'landing/hero.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $media = MediaAsset::factory()->create([
            'project_id' => $project->id,
            'uploaded_by' => $photographer->id,
        ]);
        $selection = PhotoSelection::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'media_code' => $media->media_code,
            'selected_at' => now(),
        ]);

        $this->assertStringStartsWith('LOC-', $location->location_code);
        $this->assertStringStartsWith('ROOM-', $room->room_code);
        $this->assertSame($location->location_code, $room->studio_location_code);
        $this->assertSame($location->location_code, $booking->studio_location_code);
        $this->assertSame($room->room_code, $booking->studio_room_code);
        $this->assertStringStartsWith('SCH-', $schedule->schedule_code);
        $this->assertSame($location->location_code, $schedule->studio_location_code);
        $this->assertSame($room->room_code, $schedule->studio_room_code);
        $this->assertStringStartsWith('HERO-', $slide->slide_code);
        $this->assertStringStartsWith('MEDIA-', $media->media_code);
        $this->assertStringStartsWith('SEL-', $selection->selection_code);
        $this->assertSame($media->media_code, $selection->media_code);
    }
}
