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
use App\Notifications\BookingCreatedNotification;
use App\Notifications\EditRequestSubmittedNotification;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\RawPhotosUploadedNotification;
use App\Notifications\ScheduleAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: pengiriman notifikasi saat booking baru dibuat.
     * Hasil yang diharapkan: klien, admin, dan manajer menerima notifikasi booking baru;
     * owner tidak menerima notifikasi operasional tersebut.
     */
    public function test_booking_creation_dispatches_notification_to_client_and_ops(): void
    {
        Notification::fake();
        config()->set('studio.closed_weekdays', []);

        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Notif',
            'slug' => 'cabang-notif',
            'address' => 'Jl. Notif No. 1',
            'is_active' => true,
        ]);
        StudioRoom::create([
            'studio_location_code' => $location->location_code,
            'name' => 'Studio A',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $this->actingAs($client)
            ->post('/bookings', [
                'package_id' => $package->id,
                'studio_location_code' => $location->location_code,
                'booking_date' => now()->addDays(2)->toDateString(),
                'booking_time' => '13:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
            ])
            ->assertRedirect();

        Notification::assertSentTo($client, BookingCreatedNotification::class);
        Notification::assertSentTo($admin, BookingCreatedNotification::class);
        Notification::assertSentTo($manager, BookingCreatedNotification::class);
        Notification::assertNotSentTo($owner, BookingCreatedNotification::class);
    }

    /**
     * Pengujian: pengiriman notifikasi saat admin menjadwalkan kru.
     * Hasil yang diharapkan: fotografer dan editor yang ditugaskan menerima notifikasi jadwal.
     */
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
            'studio_location_code' => $location->location_code,
            'name' => 'Studio A',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
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
                'studio_room_code' => $room->room_code,
            ])
            ->assertRedirect();

        Notification::assertSentTo($photographer, ScheduleAssignedNotification::class);
        Notification::assertSentTo($editor, ScheduleAssignedNotification::class);
    }

    /**
     * Pengujian: pengiriman notifikasi permintaan edit dari klien.
     * Hasil yang diharapkan: editor project menerima notifikasi permintaan edit.
     */
    public function test_edit_request_dispatches_notification_to_editor(): void
    {
        Notification::fake();

        [$project, $client, , $editor] = $this->makeScheduledProject();
        $project->update([
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-notif',
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001, D014',
                'edit_request_note' => 'Retouch natural.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($editor, EditRequestSubmittedNotification::class);
    }

    /**
     * Pengujian: notifikasi kepada klien pada alur Drive.
     * Hasil yang diharapkan: klien diberi notifikasi saat link foto mentah dan hasil final tersedia.
     */
    public function test_drive_workflow_dispatches_client_notifications(): void
    {
        Notification::fake();

        [$project, $client, $photographer, $editor] = $this->makeScheduledProject();

        $this->actingAs($photographer)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-notif',
            ])
            ->assertRedirect();

        Notification::assertSentTo($client, RawPhotosUploadedNotification::class);

        $project->update([
            'status' => Project::STATUS_EDITING,
            'selections_locked' => true,
            'edit_photo_codes' => 'D001',
            'edit_request_note' => 'Retouch natural.',
            'edit_requested_at' => now(),
        ]);

        $this->actingAs($editor)
            ->post("/projects/{$project->id}/assets", [
                'type' => MediaAsset::TYPE_FINAL,
                'final_drive_url' => 'https://drive.google.com/drive/folders/final-notif',
                'final_message' => 'Final tersedia.',
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
            'studio_location_code' => $location->location_code,
            'name' => 'Studio Notif',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
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
