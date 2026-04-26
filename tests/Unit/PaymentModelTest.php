<?php

namespace Tests\Unit;

use App\Models\Payment;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    public function test_status_label_maps_known_payment_statuses(): void
    {
        $cases = [
            Payment::STATUS_PENDING => 'Menunggu Pembayaran',
            Payment::STATUS_PAID => 'Lunas',
            Payment::STATUS_EXPIRED => 'Kedaluwarsa',
            Payment::STATUS_FAILED => 'Gagal',
        ];

        foreach ($cases as $status => $label) {
            $payment = new Payment(['status' => $status]);
            $this->assertSame($label, $payment->statusLabel());
        }
    }
}
