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

        $settlementBooking = Booking::factory()->create([
            'status' => Booking::STATUS_DP_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 450000,
        ]);

        Payment::query()->create([
            'booking_id' => $settlementBooking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertSame(450000, $fullBooking->nextPayableAmount());
        $this->assertSame(100000, $dpBooking->nextPayableAmount());
        $this->assertSame(350000, $settlementBooking->nextPayableAmount());
        $this->assertSame(Booking::PAYMENT_TYPE_FULL, $settlementBooking->nextPaymentType());
        $this->assertTrue($settlementBooking->isAwaitingSettlement());
    }
}
