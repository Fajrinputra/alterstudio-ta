<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\EditRequestSubmittedNotification;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\RawPhotosUploadedNotification;
use App\Notifications\ScheduleAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_dispatches_notification_to_client_and_ops(): void
    {
        Notification::fake();

        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Notif',
            'slug' => 'cabang-notif',
            'address' => 'Jl. Notif No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($client)
            ->post('/bookings', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => now()->addDays(2)->toDateString(),
                'booking_time' => '13:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
            ])
            ->assertRedirect();

        Notification::assertSentTo($client, BookingCreatedNotification::class);
        Notification::assertSentTo($admin, BookingCreatedNotification::class);
        Notification::assertSentTo($manager, BookingCreatedNotification::class);
    }

    public function test_schedule_creation_dispatches_notification_to_assigned_crew(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Jadwal',
            'slug' => 'cabang-jadwal',
            'address' => 'Jl. Jadwal No. 1',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio A',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);
        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->post("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertRedirect();

        Notification::assertSentTo($photographer, ScheduleAssignedNotification::class);
        Notification::assertSentTo($editor, ScheduleAssignedNotification::class);
    }

    public function test_finalize_selection_dispatches_edit_request_notification_to_editor(): void
    {
        Notification::fake();

        [$project, $client, , $editor] = $this->makeScheduledProject();

        $rawAsset = MediaAsset::factory()->create([
            'project_id' => $project->id,
            'uploaded_by' => $editor->id,
        ]);

        PhotoSelection::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'media_asset_id' => $rawAsset->id,
        ]);

        $this->actingAs($client)
            ->post(route('projects.selections.finalize', $project))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($editor, EditRequestSubmittedNotification::class);
    }

    public function test_media_uploads_dispatch_client_notifications(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$project, $client, $photographer, $editor] = $this->makeScheduledProject();

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'files' => [UploadedFile::fake()->image('raw-notif.jpg')],
            ])
            ->assertRedirect();

        Notification::assertSentTo($client, RawPhotosUploadedNotification::class);

        $project->update(['status' => Project::STATUS_EDITING]);

        $this->actingAs($editor)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_FINAL,
                'files' => [UploadedFile::fake()->image('final-notif.jpg')],
            ])
            ->assertRedirect();

        Notification::assertSentTo($client, FinalPhotosReadyNotification::class);
    }

    /**
     * @return array{0: Project, 1: User, 2: User, 3: User}
     */
    protected function makeScheduledProject(): array
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Workflow Notif',
            'slug' => 'cabang-workflow-notif',
            'address' => 'Jl. Workflow Notif No. 1',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Notif',
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

        return [$project, $client, $photographer, $editor];
    }
}
