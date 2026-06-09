<?php

namespace Tests\Feature\Integration;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectScheduleUser;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use App\Notifications\EditRequestSubmittedNotification;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\RawPhotosUploadedNotification;
use App\Notifications\ScheduleAssignedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingServiceLifecycleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Integration test alur utama layanan Alter Studio.
     *
     * Komponen yang diuji bersama:
     * - route dan middleware role CLIENT / ADMIN / PHOTOGRAPHER / EDITOR
     * - BookingController, PaymentController, ScheduleController,
     *   MediaAssetController, dan PhotoSelectionController
     * - relasi tabel bookings, payments, projects, project_schedules,
     *   dan project_schedule_users
     * - integrasi eksternal Midtrans yang dipalsukan dengan Http::fake()
     * - notifikasi yang dipalsukan dengan Notification::fake()
     */
    public function test_complete_booking_service_lifecycle_from_order_to_final_delivery(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.booking_buffer_minutes', 15);
        config()->set('services.midtrans.server_key', 'integration-test-server-key');
        config()->set('services.midtrans.sandbox', true);

        Notification::fake();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-integration',
            ]),
        ]);

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);

        $package = ServicePackage::factory()->create([
            'name' => 'Mini Family Integration',
            'price' => 950000,
            'duration_minutes' => 30,
            'addons' => [
                ['label' => 'Cetak 10R', 'price' => 50000, 'unit' => 'lembar'],
            ],
        ]);
        $addonKey = md5('Cetak 10R|50000');

        $location = StudioLocation::create([
            'name' => 'Cabang Integration',
            'slug' => 'cabang-integration',
            'address' => 'Jl. Integration No. 1',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Integration',
            'is_active' => true,
        ]);

        $bookingDate = Carbon::today()->addDays(3)->toDateString();

        $this->actingAs($client)
            ->postJson(route('bookings.store'), [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $bookingDate,
                'booking_time' => '11:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
                'selected_addons' => [$addonKey],
                'addon_quantities' => [$addonKey => 2],
                'notes' => 'Pengujian integrasi alur layanan.',
            ])
            ->assertCreated()
            ->assertJsonPath('display_status', 'Diajukan');

        $booking = Booking::query()->firstOrFail();
        $project = Project::query()->firstOrFail();

        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $booking->status);
        $this->assertNull($booking->confirmed_at);
        $this->assertSame($client->id, $booking->client_id);
        $this->assertSame($room->id, $booking->studio_room_id);
        $this->assertSame(100000, (int) $booking->addon_total);
        $this->assertSame(1050000, (int) $booking->total_price);
        $this->assertSame(Project::STATUS_DRAFT, $project->status);
        $this->assertSame($booking->id, $project->booking_id);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_WAITING_PAYMENT,
            ])
            ->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->confirmed_at);
        $this->assertTrue($booking->isConfirmedAwaitingPayment());

        $paymentResponse = $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertOk()
            ->assertJsonPath('snap_token', 'snap-token-integration')
            ->assertJsonPath('amount', 1050000);

        $orderId = $paymentResponse->json('order_id');

        $this->postJson('/midtrans/webhook', [
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
        ])->assertOk();

        $booking->refresh();
        $this->assertSame(Booking::STATUS_PAID, $booking->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 1050000,
            'status' => Payment::STATUS_PAID,
            'transaction_status' => 'settlement',
        ]);
        Notification::assertSentTo($client, PaymentConfirmedNotification::class);

        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertOk()
            ->assertJsonPath('status', Project::STATUS_SCHEDULED);

        $project->refresh();
        $this->assertSame(Project::STATUS_SCHEDULED, $project->status);
        $this->assertNotNull($project->start_at);
        $this->assertNotNull($project->end_at);
        $this->assertDatabaseHas('project_schedules', [
            'booking_id' => $booking->id,
            'project_id' => $project->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'scheduled_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('project_schedule_users', [
            'user_id' => $photographer->id,
            'role' => Role::PHOTOGRAPHER->value,
        ]);
        $this->assertDatabaseHas('project_schedule_users', [
            'user_id' => $editor->id,
            'role' => Role::EDITOR->value,
        ]);
        $this->assertSame(2, ProjectScheduleUser::query()->count());
        Notification::assertSentTo($photographer, ScheduleAssignedNotification::class);
        Notification::assertSentTo($editor, ScheduleAssignedNotification::class);

        $this->actingAs($photographer)
            ->post(route('projects.drive-assets.store', $project), [
                'type' => MediaAsset::TYPE_RAW,
                'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-integration',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Link Drive foto mentah berhasil disimpan. Klien telah diberi notifikasi.');

        $project->refresh();
        $this->assertSame(Project::STATUS_SHOOT_DONE, $project->status);
        $this->assertSame('https://drive.google.com/drive/folders/raw-integration', $project->raw_drive_url);
        $this->assertSame($photographer->id, $project->raw_drive_uploaded_by);
        Notification::assertSentTo($client, RawPhotosUploadedNotification::class);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001, D005, D009',
                'edit_request_note' => 'Tone warna hangat dan retouch natural.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Permintaan edit berhasil dikirim ke editor.');

        $project->refresh();
        $this->assertSame(Project::STATUS_EDITING, $project->status);
        $this->assertTrue((bool) $project->selections_locked);
        $this->assertSame('D001, D005, D009', $project->edit_photo_codes);
        Notification::assertSentTo($editor, EditRequestSubmittedNotification::class);

        $this->actingAs($editor)
            ->post(route('projects.drive-assets.store', $project), [
                'type' => MediaAsset::TYPE_FINAL,
                'final_drive_url' => 'https://drive.google.com/drive/folders/final-integration',
                'final_message' => 'Hasil final sudah tersedia.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Hasil final berhasil ditandai tersedia. Klien telah diberi notifikasi.');

        $project->refresh();
        $this->assertSame(Project::STATUS_FINAL, $project->status);
        $this->assertSame('https://drive.google.com/drive/folders/final-integration', $project->final_drive_url);
        $this->assertSame($editor->id, $project->final_drive_uploaded_by);
        Notification::assertSentTo($client, FinalPhotosReadyNotification::class);
    }
}
