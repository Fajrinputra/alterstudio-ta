<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: normalisasi add-on yang tersimpan pada model Booking.
     * Hasil yang diharapkan: add-on kosong dibuang dan subtotal dihitung dari harga serta kuantitas.
     */
    public function test_selected_addons_are_normalized_into_consistent_structure(): void
    {
        $booking = new Booking();
        $booking->setRawAttributes([
            'selected_addons' => json_encode([
                [
                    'label' => 'Extra Cetak',
                    'price' => '50000',
                    'quantity' => 2,
                    'unit' => 'lembar',
                ],
                [
                    'label' => ' Frame ',
                    'price' => 25000,
                ],
                [
                    'label' => '',
                    'price' => 99999,
                ],
            ]),
        ]);

        $addons = $booking->selected_addons;

        $this->assertCount(2, $addons);
        $this->assertSame('Extra Cetak', $addons[0]['label']);
        $this->assertSame(50000, $addons[0]['price']);
        $this->assertSame(2, $addons[0]['quantity']);
        $this->assertSame('lembar', $addons[0]['unit']);
        $this->assertSame(100000, $addons[0]['subtotal']);

        $this->assertSame('Frame', $addons[1]['label']);
        $this->assertSame(1, $addons[1]['quantity']);
        $this->assertSame(25000, $addons[1]['subtotal']);
    }

    /**
     * Pengujian: label status booking pada beberapa kondisi utama.
     * Hasil yang diharapkan: setiap status menampilkan label sesuai alur pemesanan.
     */
    public function test_status_label_reflects_booking_state_correctly(): void
    {
        $submitted = new Booking([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);
        $confirmed = new Booking([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => Carbon::now(),
        ]);
        $dpPaid = new Booking(['status' => Booking::STATUS_DP_PAID]);
        $paid = new Booking(['status' => Booking::STATUS_PAID]);
        $cancelled = new Booking(['status' => Booking::STATUS_CANCELLED]);

        $this->assertTrue($submitted->isSubmitted());
        $this->assertSame('Diajukan', $submitted->statusLabel());

        $this->assertTrue($confirmed->isConfirmedAwaitingPayment());
        $this->assertSame('Dikonfirmasi', $confirmed->statusLabel());

        $this->assertSame('DP Dibayar', $dpPaid->statusLabel());
        $this->assertSame('Lunas', $paid->statusLabel());
        $this->assertSame('Dibatalkan', $cancelled->statusLabel());
    }

    /**
     * Pengujian: perhitungan nominal terbayar dan sisa pembayaran.
     * Hasil yang diharapkan: hanya pembayaran berstatus lunas yang dihitung sebagai pembayaran sah.
     */
    public function test_paid_and_remaining_amount_only_count_paid_payments(): void
    {
        $booking = Booking::factory()->create([
            'total_price' => 500000,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 150000,
            'status' => Payment::STATUS_PENDING,
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 50000,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertSame(150000, $booking->paidAmount());
        $this->assertSame(350000, $booking->remainingAmount());
    }

    /**
     * Pengujian: nominal pembayaran berikutnya untuk pembayaran DP dan lunas.
     * Hasil yang diharapkan: DP dihitung 10% dan pelunasan menghasilkan sisa tagihan.
     */
    public function test_next_payable_amount_follows_dp_and_settlement_rules(): void
    {
        $fullBooking = new Booking([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 450000,
        ]);

        $dpBooking = new Booking([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 450000,
        ]);

        $millionDpBooking = new Booking([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 1000000,
        ]);

        $settlementBooking = Booking::factory()->create([
            'status' => Booking::STATUS_DP_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 450000,
        ]);

        Payment::query()->create([
            'booking_id' => $settlementBooking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 45000,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertSame(450000, $fullBooking->nextPayableAmount());
        $this->assertSame(45000, $dpBooking->nextPayableAmount());
        $this->assertSame(100000, $millionDpBooking->downPaymentAmount());
        $this->assertSame(405000, $settlementBooking->nextPayableAmount());
        $this->assertSame(Booking::PAYMENT_TYPE_FULL, $settlementBooking->nextPaymentType());
        $this->assertTrue($settlementBooking->isAwaitingSettlement());
    }

    /**
     * Pengujian: add-on tambah waktu pada durasi efektif booking.
     * Hasil yang diharapkan: setiap kuantitas tambah waktu menambah durasi 10 menit.
     */
    public function test_extra_time_addon_extends_effective_duration_by_ten_minutes_each(): void
    {
        $booking = Booking::factory()->create([
            'selected_addons' => [
                [
                    'label' => 'Tambah waktu',
                    'price' => 100000,
                    'quantity' => 2,
                    'subtotal' => 200000,
                ],
                [
                    'label' => 'Ganti kostum',
                    'price' => 50000,
                    'quantity' => 1,
                    'subtotal' => 50000,
                ],
            ],
        ]);
        $booking->package->update(['duration_minutes' => 30]);
        $booking->refresh()->load('package');

        $this->assertSame(20, $booking->extraDurationMinutes());
        $this->assertSame(50, $booking->effectiveDurationMinutes());
    }
}
