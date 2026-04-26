<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_creates_project_and_marks_booking_as_submitted(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.holidays', []);

        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Utama',
            'slug' => 'cabang-utama',
            'address' => 'Jl. Contoh No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $payload = [
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => Carbon::now()->addDays(2)->toDateString(),
            'booking_time' => '13:00',
            'location' => 'Studio A',
            'notes' => 'Please be on time',
            'payment_type' => 'FULL',
        ];

        $response = $this->actingAs($client)
            ->postJson('/bookings', $payload)
            ->assertCreated();

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertEquals('WAITING_PAYMENT', $booking->status);
        $this->assertNull($booking->confirmed_at);
        $this->assertEquals($package->price, $booking->total_price);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals($booking->id, $project->booking_id);
        $this->assertEquals('DRAFT', $project->status);

        $response->assertJsonPath('project.id', $project->id);
        $response->assertJsonPath('display_status', 'Diajukan');
    }

    public function test_submitted_booking_cannot_be_paid_before_admin_confirmation(): void
    {
        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Utara',
            'slug' => 'cabang-utara',
            'address' => 'Jl. Utara No. 2',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 350000,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan masih menunggu konfirmasi admin. Pembayaran belum dapat dilakukan.');
    }

    public function test_admin_can_confirm_submitted_booking_before_payment(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 420000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Tengah',
            'slug' => 'cabang-tengah-2',
            'address' => 'Jl. Tengah No. 1',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 420000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_WAITING_PAYMENT,
            ])
            ->assertRedirect();

        $this->assertNotNull($booking->fresh()->confirmed_at);
        $this->assertTrue($booking->fresh()->isConfirmedAwaitingPayment());
    }

    public function test_payment_timeout_starts_when_client_opens_payment_page(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Timer',
            'slug' => 'cabang-timer',
            'address' => 'Jl. Timer No. 5',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subMinutes(10),
            'payment_started_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 500000,
        ]);

        $this->actingAs($client)
            ->get(route('bookings.pay', $booking))
            ->assertOk();

        $this->assertNotNull($booking->fresh()->payment_started_at);
    }

    public function test_dp_paid_booking_can_create_full_settlement_snap_for_remaining_balance(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-settlement',
            ]),
        ]);

        config()->set('services.midtrans.server_key', 'test-server-key');
        config()->set('services.midtrans.sandbox', true);

        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Tengah',
            'slug' => 'cabang-tengah',
            'address' => 'Jl. Tengah No. 10',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 350000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertOk();

        $response->assertJsonPath('amount', 250000);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 250000,
            'status' => Payment::STATUS_PENDING,
            'snap_token' => 'snap-token-settlement',
        ]);
    }

    public function test_failed_settlement_does_not_remove_existing_dp_paid_status(): void
    {
        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Timur',
            'slug' => 'cabang-timur',
            'address' => 'Jl. Timur No. 20',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 350000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-2',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $settlement = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 250000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-FULL-FAIL',
            'snap_token' => 'snap-fail',
        ]);

        $this->postJson('/midtrans/webhook', [
            'order_id' => $settlement->order_id,
            'transaction_status' => 'expire',
        ])->assertOk();

        $this->assertEquals(Booking::STATUS_DP_PAID, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $settlement->fresh()->status);
    }

    public function test_paid_booking_status_cannot_be_changed_manually(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $package = ServicePackage::factory()->create(['price' => 450000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Selatan',
            'slug' => 'cabang-selatan',
            'address' => 'Jl. Selatan No. 9',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 450000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 450000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-PAID-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect();

        $this->assertEquals(Booking::STATUS_PAID, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_PAID, $booking->payments()->first()->status);
    }

    public function test_failed_initial_payment_returns_booking_to_waiting_payment(): void
    {
        $package = ServicePackage::factory()->create(['price' => 280000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Barat',
            'slug' => 'cabang-barat',
            'address' => 'Jl. Barat No. 7',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 280000,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 280000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-FULL-DENY',
            'snap_token' => 'snap-deny',
        ]);

        $this->postJson('/midtrans/webhook', [
            'order_id' => $payment->order_id,
            'transaction_status' => 'deny',
        ])->assertOk();

        $this->assertEquals(Booking::STATUS_WAITING_PAYMENT, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_FAILED, $payment->fresh()->status);
    }
}
