<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use App\Enums\Role;
use App\Notifications\InactiveClientAccountDeletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenanceCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: command pembatalan otomatis booking yang melewati batas pembayaran.
     * Hasil yang diharapkan: hanya booking yang payment window-nya sudah mulai dan kedaluwarsa yang dibatalkan.
     */
    public function test_cancel_expired_bookings_command_cancels_only_started_expired_payment_windows(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Command',
            'slug' => 'cabang-command',
            'address' => 'Jl. Command',
            'is_active' => true,
        ]);

        $expired = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_started_at' => now()->subMinutes(31),
        ]);
        $active = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_started_at' => now()->subMinutes(10),
        ]);
        $notStarted = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_started_at' => null,
        ]);

        Payment::create([
            'booking_id' => $expired->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 100000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-EXPIRED',
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutput('Expired bookings cancelled: 1')
            ->assertExitCode(0);

        $this->assertSame(Booking::STATUS_CANCELLED, $expired->fresh()->status);
        $this->assertSame(Payment::STATUS_EXPIRED, $expired->payments()->first()->status);
        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $active->fresh()->status);
        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $notStarted->fresh()->status);
    }

    /**
     * Pengujian: command pembersihan aset media yang sudah kedaluwarsa.
     * Hasil yang diharapkan: hanya file dan record yang melewati masa berlaku yang dihapus.
     */
    public function test_cleanup_expired_media_command_deletes_expired_files_and_records_only(): void
    {
        Storage::fake('public');

        $project = Project::factory()->create();
        Storage::disk('public')->put('projects/1/expired.jpg', 'expired');
        Storage::disk('public')->put('projects/1/current.jpg', 'current');

        $expired = MediaAsset::factory()->create([
            'project_id' => $project->id,
            'path' => 'projects/1/expired.jpg',
            'expires_at' => now()->subDay(),
        ]);
        $current = MediaAsset::factory()->create([
            'project_id' => $project->id,
            'path' => 'projects/1/current.jpg',
            'expires_at' => now()->addDay(),
        ]);

        $this->artisan('media:cleanup-expired')
            ->expectsOutput('Aset media kedaluwarsa yang dibersihkan: 1')
            ->assertExitCode(0);

        Storage::disk('public')->assertMissing('projects/1/expired.jpg');
        Storage::disk('public')->assertExists('projects/1/current.jpg');
        $this->assertDatabaseMissing('media_assets', ['media_code' => $expired->media_code]);
        $this->assertDatabaseHas('media_assets', ['media_code' => $current->media_code]);
    }

    /**
     * Pengujian: command pemrosesan akun klien tidak aktif.
     * Hasil yang diharapkan: hanya klien tanpa transaksi terbaru yang diberi notifikasi dan dihapus.
     */
    public function test_cleanup_inactive_clients_notifies_and_deletes_only_clients_without_recent_transactions(): void
    {
        Notification::fake();

        $inactive = User::factory()->create([
            'role' => Role::CLIENT,
            'created_at' => now()->subMonths(7),
        ]);
        $recent = User::factory()->create([
            'role' => Role::CLIENT,
            'created_at' => now()->subMonths(7),
        ]);
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
            'created_at' => now()->subMonths(7),
        ]);

        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Inactive Cleanup',
            'slug' => 'cabang-inactive-cleanup',
            'address' => 'Jl. Cleanup',
            'is_active' => true,
        ]);

        Booking::factory()->create([
            'client_id' => $recent->id,
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'created_at' => now()->subMonth(),
        ]);

        $this->artisan('clients:cleanup-inactive')
            ->expectsOutput('Akun klien tidak aktif yang diproses: 1')
            ->assertExitCode(0);

        Notification::assertSentTo($inactive, InactiveClientAccountDeletedNotification::class);
        Notification::assertNotSentTo($recent, InactiveClientAccountDeletedNotification::class);
        Notification::assertNotSentTo($admin, InactiveClientAccountDeletedNotification::class);

        $this->assertDatabaseMissing('users', ['id' => $inactive->id]);
        $this->assertDatabaseHas('users', ['id' => $recent->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
