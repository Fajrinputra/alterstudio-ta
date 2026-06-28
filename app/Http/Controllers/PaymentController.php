<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentConfirmedNotification;
use App\Support\DeferredNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Menangani pembuatan transaksi Midtrans dan sinkronisasi status pembayaran.
 */
class PaymentController extends Controller
{
    /** Membuat token Snap untuk pembayaran klien. */
    public function createSnap(Request $request, Booking $booking)
    {
        $this->authorizeBooking($request, $booking);

        $validated = $request->validate([
            'type' => ['required', 'in:' . Payment::TYPE_DP . ',' . Payment::TYPE_FULL],
        ]);

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Pemesanan sudah dibatalkan dan tidak dapat dibayar kembali.',
            ], 422);
        }

        if ($booking->isSubmitted()) {
            return response()->json([
                'message' => 'Pemesanan masih menunggu konfirmasi admin atau manajer. Pembayaran belum dapat dilakukan.',
            ], 422);
        }

        if ($this->cancelIfPaymentWindowExpired($booking)) {
            return response()->json([
                'message' => 'Waktu pembayaran 30 menit sudah habis. Pemesanan dibatalkan otomatis, silakan pesan ulang.',
            ], 422);
        }

        if ($booking->status === Booking::STATUS_PAID) {
            return response()->json([
                'message' => 'Pemesanan sudah lunas dan tidak memerlukan pembayaran tambahan.',
            ], 422);
        }

        $expectedType = $booking->nextPaymentType();
        if ($validated['type'] !== $expectedType) {
            return response()->json([
                'message' => $booking->status === Booking::STATUS_DP_PAID
                    ? 'DP sudah dibayar. Lanjutkan dengan pelunasan sisa pembayaran.'
                    : 'Jenis pembayaran tidak sesuai dengan pilihan pemesanan.',
            ], 422);
        }

        $isSettlement = $booking->status === Booking::STATUS_DP_PAID && $validated['type'] === Payment::TYPE_FULL;
        $amount = $isSettlement
            ? $booking->remainingAmount()
            : $booking->nextPayableAmount();

        if ($amount <= 0) {
            return response()->json([
                'message' => 'Sisa pembayaran sudah tidak ada.',
            ], 422);
        }

        // Gunakan transaksi pending yang masih aktif agar tidak membuat data ganda
        // ketika pengguna menekan tombol bayar lebih dari satu kali.
        $existingPending = Payment::where('booking_id', $booking->id)
            ->where('type', $validated['type'])
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('snap_token')
            ->latest()
            ->first();

        if ($existingPending) {
            return response()->json([
                'snap_token' => $existingPending->snap_token,
                'order_id' => $existingPending->order_id,
                'payment' => $existingPending,
                'amount' => $existingPending->amount,
                'reused' => true,
            ]);
        }

        $orderId = 'ORDER-'.$booking->id.'-'.Str::uuid();

        // Kirim permintaan pembuatan transaksi ke Midtrans.
        $serverKey = config('services.midtrans.server_key');
        if (!is_string($serverKey) || trim($serverKey) === '') {
            Log::error('Midtrans configuration missing: MIDTRANS_SERVER_KEY');
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum lengkap (MIDTRANS_SERVER_KEY).',
            ], 422);
        }
        $isSandbox = (bool) config('services.midtrans.sandbox', true);
        $baseUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com'
            : 'https://app.midtrans.com';

        if ($isSettlement) {
            $itemDetails = [[
                'id' => 'settlement-'.$booking->id,
                'price' => $amount,
                'quantity' => 1,
                'name' => 'Pelunasan Pemesanan #'.$booking->id,
            ]];
        } else {
            $itemDetails = [[
                'id' => $booking->package_id,
                'price' => $validated['type'] === Payment::TYPE_FULL ? (int) $booking->package->price : $amount,
                'quantity' => 1,
                'name' => ($booking->package->name ?? 'Paket').($validated['type'] === Payment::TYPE_DP ? ' (DP)' : ''),
            ]];
        }

        if (! $isSettlement && $validated['type'] === Payment::TYPE_FULL && !empty($booking->selected_addons)) {
            foreach ($booking->selected_addons as $idx => $addon) {
                $price = (int) ($addon['price'] ?? 0);
                $quantity = max(1, (int) ($addon['quantity'] ?? 1));
                if ($price <= 0) {
                    continue;
                }
                $itemDetails[] = [
                    'id' => 'addon-'.$booking->id.'-'.$idx,
                    'price' => $price,
                    'quantity' => $quantity,
                    'name' => (string) ($addon['label'] ?? 'Addon'),
                ];
            }
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'enabled_payments' => [
                'bank_transfer',
                'gopay',
                'qris',
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $booking->client->name ?? 'Client',
                'email' => $booking->client->email ?? 'client@example.com',
            ],
            'callbacks' => [
                'finish' => route('bookings.index'),
            ],
            'credit_card' => [
                'secure' => true,
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->post($baseUrl.'/snap/v1/transactions', $payload)
                ->throw();
        } catch (\Throwable $e) {
            $body = method_exists($e, 'response') && $e->response ? $e->response->body() : $e->getMessage();
            Log::error('Midtrans Snap error', ['error' => $body]);
            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans',
                'detail' => $body,
            ], 422);
        }

        $snapToken = $response['token'] ?? null;
        if (!$snapToken) {
            Log::error('Midtrans Snap token kosong', ['body' => $response->body()]);
            return response()->json(['message' => 'Gagal membuat transaksi Midtrans'], 422);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => $validated['type'],
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'order_id' => $orderId,
            'snap_token' => $snapToken,
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $orderId,
            'payment' => $payment,
            'amount' => $amount,
        ]);
    }

    /** Menerima webhook Midtrans dan menyamakan status internal. */
    public function webhook(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:100'],
            'transaction_status' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'signature_key' => ['required', 'string', 'size:128'],
        ]);

        $serverKey = config('services.midtrans.server_key');
        if (! is_string($serverKey) || trim($serverKey) === '') {
            Log::error('Midtrans webhook rejected because server key is missing.');

            return response()->json(['message' => 'Konfigurasi Midtrans belum lengkap.'], 503);
        }

        // Signature dan nominal dicek agar callback palsu atau nominal berbeda ditolak.
        $expectedSignature = hash(
            'sha512',
            $data['order_id'].$data['status_code'].$data['gross_amount'].$serverKey
        );

        if (! hash_equals($expectedSignature, $data['signature_key'])) {
            Log::warning('Invalid Midtrans webhook signature.', ['order_id' => $data['order_id']]);

            return response()->json(['message' => 'Signature webhook tidak valid.'], 403);
        }

        $payment = Payment::where('order_id', $data['order_id'])->firstOrFail();
        if (abs((float) $payment->amount - (float) $data['gross_amount']) > 0.01) {
            Log::warning('Midtrans webhook amount mismatch.', [
                'order_id' => $data['order_id'],
                'expected_amount' => $payment->amount,
                'received_amount' => $data['gross_amount'],
            ]);

            return response()->json(['message' => 'Nominal webhook tidak sesuai.'], 422);
        }

        $this->applyStatus($payment, $data['transaction_status']);

        Log::info('Midtrans webhook handled', [
            'order_id' => $data['order_id'],
            'status' => $data['transaction_status'],
        ]);

        return response()->json(['message' => 'ok']);
    }

    /** Verifikasi ulang status transaksi dari sisi server setelah popup Snap ditutup. */
    public function confirm(Request $request, Booking $booking)
    {
        $this->authorizeBooking($request, $booking);

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Pemesanan sudah dibatalkan.',
                'booking_status' => $booking->status,
            ], 422);
        }

        if ($booking->isSubmitted()) {
            return response()->json([
                'message' => 'Pemesanan masih menunggu konfirmasi admin atau manajer.',
                'booking_status' => $booking->status,
            ], 422);
        }

        if ($this->cancelIfPaymentWindowExpired($booking)) {
            return response()->json([
                'message' => 'Waktu pembayaran 30 menit sudah habis. Pemesanan dibatalkan otomatis.',
                'booking_status' => $booking->fresh()->status,
            ], 422);
        }

        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Tidak ada transaksi pembayaran yang sedang diproses.',
                'booking_status' => $booking->status,
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
        if (!is_string($serverKey) || trim($serverKey) === '') {
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum lengkap (MIDTRANS_SERVER_KEY).',
            ], 422);
        }
        $isSandbox = (bool) config('services.midtrans.sandbox', true);
        $baseUrl = $isSandbox
            ? 'https://api.sandbox.midtrans.com'
            : 'https://api.midtrans.com';

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->get($baseUrl.'/v2/'.$payment->order_id.'/status')
                ->throw();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal verifikasi status pembayaran.',
                'detail' => $e->getMessage(),
            ], 422);
        }

        $transactionStatus = (string) ($response['transaction_status'] ?? 'pending');
        $this->applyStatus($payment, $transactionStatus);

        return response()->json([
            'message' => 'Status pembayaran diperbarui.',
            'booking_status' => $booking->fresh()->status,
            'transaction_status' => $transactionStatus,
        ]);
    }

    /** Hanya pemilik pemesanan atau admin yang boleh memproses pembayaran. */
    protected function authorizeBooking(Request $request, Booking $booking): void
    {
        $user = $request->user();
        if ($user->id !== $booking->client_id && $user->role !== \App\Enums\Role::ADMIN) {
            abort(403);
        }
    }

    protected function applyStatus(Payment $payment, string $transactionStatus): void
    {
        $booking = $payment->booking;
        $previousStatus = $payment->status;

        // Ubah status bawaan Midtrans ke status pembayaran internal aplikasi.
        $statusMap = [
            'settlement' => Payment::STATUS_PAID,
            'capture' => Payment::STATUS_PAID,
            'pending' => Payment::STATUS_PENDING,
            'expire' => Payment::STATUS_EXPIRED,
            'cancel' => Payment::STATUS_FAILED,
            'failure' => Payment::STATUS_FAILED,
            'deny' => Payment::STATUS_FAILED,
        ];

        $newStatus = $statusMap[$transactionStatus] ?? Payment::STATUS_PENDING;

        $payment->update([
            'transaction_status' => $transactionStatus,
            'status' => $newStatus,
            'paid_at' => $newStatus === Payment::STATUS_PAID ? Carbon::now() : null,
        ]);

        if ($newStatus === Payment::STATUS_PAID) {
            $booking->status = $payment->type === Payment::TYPE_DP ? Booking::STATUS_DP_PAID : Booking::STATUS_PAID;
            $booking->save();

            if ($previousStatus !== Payment::STATUS_PAID) {
                DeferredNotification::to($booking->client, new PaymentConfirmedNotification($payment->id));
            }
        } elseif (in_array($newStatus, [Payment::STATUS_EXPIRED, Payment::STATUS_FAILED], true)) {
            $booking->status = $booking->remainingAmount() > 0 && $booking->paidAmount() > 0
                ? Booking::STATUS_DP_PAID
                : Booking::STATUS_WAITING_PAYMENT;
            $booking->save();
        }
    }

    protected function cancelIfPaymentWindowExpired(Booking $booking): bool
    {
        // Window 30 menit menjaga slot tidak tertahan terlalu lama oleh transaksi kosong.
        if (! $booking->isPaymentWindowExpired()) {
            return false;
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        $booking->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->update([
                'status' => Payment::STATUS_EXPIRED,
                'transaction_status' => 'payment_window_expired',
                'paid_at' => null,
            ]);

        return true;
    }
}
