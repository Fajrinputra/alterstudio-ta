<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaWorkflowStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_uploading_raw_marks_project_as_shoot_done(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$project, $photographer] = $this->makeScheduledProject();

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'files' => [UploadedFile::fake()->image('raw-1.jpg')],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Upload berhasil disimpan.');

        $this->assertEquals(Project::STATUS_SHOOT_DONE, $project->fresh()->status);
        $this->assertDatabaseHas('media_assets', [
            'project_id' => $project->id,
            'type' => MediaAsset::TYPE_RAW,
        ]);
    }

    public function test_editor_uploading_final_marks_project_as_final(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$project, , $editor] = $this->makeScheduledProject();
        $project->update(['status' => Project::STATUS_EDITING]);

        $this->actingAs($editor)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_FINAL,
                'files' => [UploadedFile::fake()->image('final-1.jpg')],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Upload berhasil disimpan.');

        $this->assertEquals(Project::STATUS_FINAL, $project->fresh()->status);
        $this->assertDatabaseHas('media_assets', [
            'project_id' => $project->id,
            'type' => MediaAsset::TYPE_FINAL,
        ]);
    }

    /**
     * @return array{0: Project, 1: User, 2: User}
     */
    protected function makeScheduledProject(): array
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Workflow',
            'slug' => 'cabang-workflow',
            'address' => 'Jl. Workflow No. 1',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Workflow',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        return [$project, $photographer, $editor];
    }
}
