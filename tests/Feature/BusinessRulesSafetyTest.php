<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessRulesSafetyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: perlindungan akun kru yang masih memiliki project aktif.
     * Hasil yang diharapkan: Owner tidak dapat menonaktifkan pengguna yang masih terhubung project berjalan.
     */
    public function test_owner_cannot_deactivate_user_with_active_project(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $photographer = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
            'is_active' => true,
        ]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $category = ServiceCategory::create(['name' => 'Wedding']);
        $package = ServicePackage::factory()->create(['category_id' => $category->id]);
        $location = StudioLocation::create(['name' => 'Cabang A', 'slug' => 'cabang-a', 'is_active' => true]);

        $booking = Booking::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(1),
            'booking_time' => '13:00',
            'status' => Booking::STATUS_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'addon_total' => 0,
            'total_price' => 200000,
        ]);

        Project::create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($owner)
            ->post(route('admin.users.toggle', $photographer), ['is_active' => 0])
            ->assertRedirect();

        $this->assertTrue($photographer->fresh()->is_active);
    }

    /**
     * Pengujian: penghapusan akun mandiri melalui profil.
     * Hasil yang diharapkan: route hapus profil tidak tersedia dan akun pengguna tetap ada.
     */
    public function test_profile_self_delete_is_not_available(): void
    {
        $client = User::factory()->create([
            'role' => Role::CLIENT,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($client)
            ->delete('/profile')
            ->assertStatus(405);

        $this->assertDatabaseHas('users', ['id' => $client->id]);
    }

    /**
     * Pengujian: booking menunggu pembayaran yang melewati batas waktu.
     * Hasil yang diharapkan: booking otomatis dibatalkan dan pembayaran pending menjadi kedaluwarsa.
     */
    public function test_expired_waiting_payment_booking_is_cancelled_automatically(): void
    {
        Carbon::setTestNow(now());

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $category = ServiceCategory::create(['name' => 'Family']);
        $package = ServicePackage::factory()->create(['category_id' => $category->id]);
        $location = StudioLocation::create(['name' => 'Cabang C', 'slug' => 'cabang-c', 'is_active' => true]);

        $booking = Booking::query()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(3),
            'booking_time' => '12:00',
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subMinutes(31),
            'payment_started_at' => now()->subMinutes(31),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'addon_total' => 0,
            'total_price' => 400000,
        ]);

        $booking->forceFill([
            'created_at' => now()->subMinutes(32),
            'updated_at' => now()->subMinutes(31),
        ])->saveQuietly();

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 400000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-TEST-1',
            'snap_token' => 'SNAP-TEST',
        ]);

        $this->artisan('bookings:cancel-expired')
            ->assertExitCode(0);

        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $booking->payments()->first()->status);

        Carbon::setTestNow();
    }
}
