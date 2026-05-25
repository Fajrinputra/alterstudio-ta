<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentControllerEdgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_snap_rejects_cancelled_paid_wrong_type_and_missing_midtrans_key(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $cancelled = $this->bookingFor($client, Booking::STATUS_CANCELLED);
        $paid = $this->bookingFor($client, Booking::STATUS_PAID);
        $dpPaid = $this->bookingFor($client, Booking::STATUS_DP_PAID, Booking::PAYMENT_TYPE_DP);
        Payment::create([
            'booking_id' => $dpPaid->id,
            'type' => Payment::TYPE_DP,
            'amount' => 50000,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);
        $ready = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $ready->update(['confirmed_at' => now(), 'payment_started_at' => now()]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $cancelled), ['type' => Payment::TYPE_FULL])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan sudah dibatalkan dan tidak dapat dibayar kembali.');

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $paid), ['type' => Payment::TYPE_FULL])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan sudah lunas dan tidak memerlukan pembayaran tambahan.');

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $dpPaid), ['type' => Payment::TYPE_DP])
            ->assertStatus(422)
            ->assertJsonPath('message', 'DP sudah dibayar. Lanjutkan dengan pelunasan sisa pembayaran.');

        config()->set('services.midtrans.server_key', '');
        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $ready), ['type' => Payment::TYPE_FULL])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Konfigurasi Midtrans belum lengkap (MIDTRANS_SERVER_KEY).');
    }

    public function test_create_snap_handles_midtrans_error_and_empty_token(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $booking = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $booking->update([
            'confirmed_at' => now(),
            'payment_started_at' => now(),
            'selected_addons' => [
                ['label' => 'Cetak', 'price' => 25000, 'quantity' => 2],
                ['label' => 'Gratis', 'price' => 0, 'quantity' => 1],
            ],
            'addon_total' => 50000,
            'total_price' => 550000,
        ]);

        config()->set('services.midtrans.server_key', 'server-key');
        config()->set('services.midtrans.sandbox', true);
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['error' => 'bad'], 500),
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => Payment::TYPE_FULL])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Gagal membuat transaksi Midtrans');

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['not_token' => true]),
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => Payment::TYPE_FULL])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Gagal membuat transaksi Midtrans');

    }

    public function test_create_snap_supports_full_addons_and_reuses_pending_payment(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $booking = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $booking->update([
            'confirmed_at' => now(),
            'payment_started_at' => now(),
            'selected_addons' => [
                ['label' => 'Cetak', 'price' => 25000, 'quantity' => 2],
                ['label' => 'Gratis', 'price' => 0, 'quantity' => 1],
            ],
            'addon_total' => 50000,
            'total_price' => 550000,
        ]);

        config()->set('services.midtrans.server_key', 'server-key');
        config()->set('services.midtrans.sandbox', true);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'snap-full']),
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => Payment::TYPE_FULL]);
        $response->assertOk()
            ->assertJsonPath('snap_token', 'snap-full')
            ->assertJsonPath('amount', 550000);

        Http::assertSent(fn ($request) => data_get($request->data(), 'transaction_details.gross_amount') === 550000
            && count(data_get($request->data(), 'item_details')) === 2);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => Payment::TYPE_FULL])
            ->assertOk()
            ->assertJsonPath('reused', true)
            ->assertJsonPath('snap_token', 'snap-full');
    }

    public function test_confirm_handles_all_guard_paths_and_successful_status_lookup(): void
    {
        Notification::fake();
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $cancelled = $this->bookingFor($client, Booking::STATUS_CANCELLED);
        $submitted = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $noPending = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $noPending->update(['confirmed_at' => now(), 'payment_started_at' => now()]);
        $missingKey = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $missingKey->update(['confirmed_at' => now(), 'payment_started_at' => now()]);
        Payment::create([
            'booking_id' => $missingKey->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-MISSING-KEY',
        ]);

        $this->actingAs($client)->postJson(route('bookings.pay.confirm', $cancelled))->assertStatus(422);
        $this->actingAs($client)->postJson(route('bookings.pay.confirm', $submitted))->assertStatus(422);
        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $noPending))
            ->assertOk()
            ->assertJsonPath('message', 'Tidak ada transaksi pembayaran yang sedang diproses.');

        config()->set('services.midtrans.server_key', '');
        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $missingKey))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Konfigurasi Midtrans belum lengkap (MIDTRANS_SERVER_KEY).');

        $httpFail = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $httpFail->update(['confirmed_at' => now(), 'payment_started_at' => now()]);
        Payment::create([
            'booking_id' => $httpFail->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-HTTP-FAIL',
        ]);
        config()->set('services.midtrans.server_key', 'server-key');
        Http::fake(['https://api.sandbox.midtrans.com/v2/ORDER-HTTP-FAIL/status' => Http::response([], 500)]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $httpFail))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Gagal verifikasi status pembayaran.');

        $success = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $success->update(['confirmed_at' => now(), 'payment_started_at' => now()]);
        Payment::create([
            'booking_id' => $success->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-OK',
        ]);
        Http::fake(['https://api.sandbox.midtrans.com/v2/ORDER-OK/status' => Http::response(['transaction_status' => 'capture'])]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $success))
            ->assertOk()
            ->assertJsonPath('booking_status', Booking::STATUS_PAID)
            ->assertJsonPath('transaction_status', 'capture');
    }

    public function test_payment_authorization_and_unknown_webhook_status_are_handled(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $other = User::factory()->create(['role' => Role::CLIENT]);
        $booking = $this->bookingFor($client, Booking::STATUS_WAITING_PAYMENT);
        $booking->update(['confirmed_at' => now(), 'payment_started_at' => now()]);

        $this->actingAs($other)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => Payment::TYPE_FULL])
            ->assertForbidden();

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-UNKNOWN',
        ]);

        $this->postJson('/midtrans/webhook', [
            'order_id' => $payment->order_id,
            'transaction_status' => 'mystery',
        ])->assertOk();

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    private function bookingFor(User $client, string $status, string $paymentType = Booking::PAYMENT_TYPE_FULL): Booking
    {
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Payment '.uniqid(),
            'slug' => 'payment-'.uniqid(),
            'address' => 'Jl. Payment',
            'is_active' => true,
        ]);

        return Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => $status,
            'payment_type' => $paymentType,
            'total_price' => 500000,
        ]);
    }
}
