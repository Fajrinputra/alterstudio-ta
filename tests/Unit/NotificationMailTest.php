<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\EditRequestSubmittedNotification;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\RawPhotosUploadedNotification;
use App\Notifications\ScheduleAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationMailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: isi email notifikasi setelah booking dikonfirmasi admin.
     * Hasil yang diharapkan: email berisi subjek, pesan, dan tombol menuju pembayaran.
     */
    public function test_booking_confirmed_notification_builds_payment_mail(): void
    {
        [$booking, , $client] = $this->makeBooking();

        $mail = (new BookingConfirmedNotification($booking))->toMail($client);

        $this->assertSame(['mail'], (new BookingConfirmedNotification($booking))->via($client));
        $this->assertSame('[Alter Studio] Pemesanan #'.$booking->id.' dikonfirmasi', $mail->subject);
        $this->assertContains('Pemesanan Anda sudah dikonfirmasi oleh admin dan siap dilanjutkan ke pembayaran.', $mail->introLines);
        $this->assertSame('Lanjutkan Pembayaran', $mail->actionText);
    }

    /**
     * Pengujian: template email saat booking baru dibuat oleh klien.
     * Hasil yang diharapkan: notifikasi memakai markdown booking dan data penerima klien terbaca.
     */
    public function test_booking_created_notification_uses_booking_markdown_template(): void
    {
        [$booking, $package, $client] = $this->makeBooking();

        $mail = (new BookingCreatedNotification($booking))->toMail($client);

        $this->assertSame(['mail'], (new BookingCreatedNotification($booking))->via($client));
        $this->assertSame("[Alter Studio] Pemesanan #{$booking->id} - {$package->name}", $mail->subject);
        $this->assertSame('mail.booking.created', $mail->markdown);
        $this->assertTrue($mail->viewData['isClientRecipient']);
    }

    /**
     * Pengujian: isi email konfirmasi pembayaran.
     * Hasil yang diharapkan: email memuat jenis pembayaran dan nominal yang berhasil dibayar.
     */
    public function test_payment_confirmed_notification_contains_payment_detail(): void
    {
        [$booking, , $client] = $this->makeBooking();
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-NOTIF',
            'paid_at' => now(),
        ]);

        $mail = (new PaymentConfirmedNotification($payment->id))->toMail($client);

        $this->assertSame('[Alter Studio] Pembayaran berhasil dikonfirmasi', $mail->subject);
        $this->assertContains('Jenis pembayaran: DP', $mail->introLines);
        $this->assertContains('Nominal: Rp 100.000', $mail->introLines);
    }

    /**
     * Pengujian: isi email penugasan jadwal untuk kru.
     * Hasil yang diharapkan: kru menerima pesan penugasan dan tombol menuju jadwal.
     */
    public function test_schedule_assigned_notification_contains_schedule_context(): void
    {
        [$booking] = $this->makeBooking();
        $crew = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'name' => 'Crew A']);
        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'photographer_id' => $crew->id,
            'status' => Project::STATUS_SCHEDULED,
            'start_at' => now()->addDay()->setTime(10, 0),
            'end_at' => now()->addDay()->setTime(11, 0),
        ]);

        $mail = (new ScheduleAssignedNotification($project->id))->toMail($crew);

        $this->assertSame('[Alter Studio] Penugasan jadwal baru', $mail->subject);
        $this->assertContains('Anda mendapatkan penugasan baru di Alter Studio.', $mail->introLines);
        $this->assertSame('Lihat Jadwal', $mail->actionText);
    }

    /**
     * Pengujian: email notifikasi pada alur Drive dan hasil final.
     * Hasil yang diharapkan: klien/editor menerima pesan sesuai tahapan foto mentah, edit, dan final.
     */
    public function test_drive_workflow_notifications_build_expected_mail_messages(): void
    {
        [$booking, $package, $client] = $this->makeBooking();
        $editor = User::factory()->create(['role' => Role::EDITOR, 'name' => 'Editor Notif']);
        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'editor_id' => $editor->id,
            'status' => Project::STATUS_EDITING,
        ]);

        $rawMail = (new RawPhotosUploadedNotification($project->id))->toMail($client);
        $editMail = (new EditRequestSubmittedNotification($project->id))->toMail($editor);
        $finalMail = (new FinalPhotosReadyNotification($project->id))->toMail($client);

        $this->assertSame('[Alter Studio] Link foto mentah telah tersedia', $rawMail->subject);
        $this->assertContains('Paket: '.$package->name, $rawMail->introLines);
        $this->assertContains('Link Drive ini berlaku selama 3 hari sejak link dibagikan. Silakan segera buka folder Drive dan catat kode foto yang ingin diedit.', $rawMail->introLines);

        $this->assertSame('[Alter Studio] Permintaan edit baru dari klien', $editMail->subject);
        $this->assertContains('Klien: '.$client->name, $editMail->introLines);
        $this->assertSame('Buka Detail Project', $editMail->actionText);

        $this->assertSame('[Alter Studio] Foto final Anda sudah siap', $finalMail->subject);
        $this->assertContains('Editor sudah menandai hasil edit final tersedia di folder Drive project.', $finalMail->introLines);
        $this->assertContains('Link Drive hasil final berlaku selama 3 hari sejak hasil dibagikan. Silakan segera buka dan unduh file Anda.', $finalMail->introLines);
    }

    /**
     * @return array{0: Booking, 1: ServicePackage, 2: User}
     */
    private function makeBooking(): array
    {
        $client = User::factory()->create(['role' => Role::CLIENT, 'name' => 'Client Notif']);
        $package = ServicePackage::factory()->create(['name' => 'Paket Notifikasi', 'price' => 1000000]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 1000000,
        ]);

        return [$booking, $package, $client];
    }
}
