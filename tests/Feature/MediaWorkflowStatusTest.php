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
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MediaWorkflowStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_storing_raw_drive_link_marks_project_as_shoot_done(): void
    {
        Notification::fake();

        [$project, $photographer] = $this->makeScheduledProject();

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Link Drive foto mentah berhasil disimpan. Klien telah diberi notifikasi.');

        $this->assertEquals(Project::STATUS_SHOOT_DONE, $project->fresh()->status);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
        ]);
    }

    public function test_cancelled_booking_cannot_start_raw_drive_workflow(): void
    {
        Notification::fake();

        [$project, $photographer] = $this->makeScheduledProject();
        $project->booking->update(['status' => Booking::STATUS_CANCELLED]);

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.');

        $this->assertNull($project->fresh()->raw_drive_url);
    }

    public function test_dp_paid_booking_cannot_start_raw_drive_workflow(): void
    {
        Notification::fake();

        [$project, $photographer] = $this->makeScheduledProject();
        $project->booking->update(['status' => Booking::STATUS_DP_PAID]);

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Proses pasca-produksi hanya dapat dilanjutkan setelah pembayaran lunas.');

        $project->refresh();

        $this->assertNull($project->raw_drive_url);
        $this->assertEquals(Project::STATUS_SCHEDULED, $project->status);
    }

    public function test_unscheduled_project_cannot_start_raw_drive_workflow(): void
    {
        Notification::fake();

        [$project, $photographer] = $this->makeScheduledProject();
        $project->update([
            'status' => Project::STATUS_DRAFT,
            'start_at' => null,
            'end_at' => null,
        ]);

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Proses pasca-produksi hanya dapat dilanjutkan setelah admin menjadwalkan project.');

        $this->assertNull($project->fresh()->raw_drive_url);
    }

    public function test_editor_marking_final_drive_link_marks_project_as_final(): void
    {
        Notification::fake();

        [$project, , $editor] = $this->makeScheduledProject();
        $project->update([
            'status' => Project::STATUS_EDITING,
            'selections_locked' => true,
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            'raw_drive_uploaded_at' => now(),
            'edit_photo_codes' => 'D001, D005',
            'edit_request_note' => 'Retouch natural',
            'edit_requested_at' => now(),
        ]);

        $this->actingAs($editor)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_FINAL,
                'final_drive_url' => 'https://drive.google.com/drive/folders/final-project',
                'final_message' => 'Hasil final sudah tersedia.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Hasil final berhasil ditandai tersedia. Klien telah diberi notifikasi.');

        $this->assertEquals(Project::STATUS_FINAL, $project->fresh()->status);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'final_drive_url' => 'https://drive.google.com/drive/folders/final-project',
        ]);
    }

    public function test_cancelled_booking_cannot_mark_final_drive_link(): void
    {
        Notification::fake();

        [$project, , $editor] = $this->makeScheduledProject();
        $project->update([
            'status' => Project::STATUS_EDITING,
            'selections_locked' => true,
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            'raw_drive_uploaded_at' => now(),
            'edit_photo_codes' => 'D001, D005',
            'edit_request_note' => 'Retouch natural',
            'edit_requested_at' => now(),
        ]);
        $project->booking->update(['status' => Booking::STATUS_CANCELLED]);

        $this->actingAs($editor)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_FINAL,
                'final_drive_url' => 'https://drive.google.com/drive/folders/final-project',
                'final_message' => 'Hasil final sudah tersedia.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.');

        $this->assertNull($project->fresh()->final_drive_url);
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
