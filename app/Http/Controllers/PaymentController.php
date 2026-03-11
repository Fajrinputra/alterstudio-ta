<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Integrasi pembayaran Midtrans + sinkron status booking.
 */
class PaymentController extends Controller
{
    /** Generate token Snap (dummy placeholder) untuk bayar booking client/admin. */
    public function createSnap(Request $request, Booking $booking)
    {
        $this->authorizeBooking($request, $booking);

        if (in_array($booking->status, ['PAID', 'DP_PAID'], true)) {
            return response()->json([
                'message' => 'Booking sudah memiliki pembayaran terkonfirmasi.',
            ], 422);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:DP,FULL'],
        ]);

        $amount = $validated['type'] === 'DP'
            ? (int) min($booking->total_price, 100000)
            : (int) $booking->total_price;

        // Reuse transaksi pending agar tidak membuat multiple payment record
        // ketika user klik bayar berulang.
        $existingPending = Payment::where('booking_id', $booking->id)
            ->where('type', $validated['type'])
            ->where('status', 'PENDING')
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

        // Call Midtrans Snap API (sandbox)
        $serverKey = config('services.midtrans.server_key');
        $isSandbox = (bool) config('services.midtrans.sandbox', true);
        $baseUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com'
            : 'https://app.midtrans.com';

        $itemDetails = [
            [
                'id' => $booking->package_id,
                'price' => $validated['type'] === 'FULL' ? (int) $booking->package->price : $amount,
                'quantity' => 1,
                'name' => ($booking->package->name ?? 'Paket').($validated['type'] === 'DP' ? ' (DP)' : ''),
            ]
        ];

        if ($validated['type'] === 'FULL' && !empty($booking->selected_addons)) {
            foreach ($booking->selected_addons as $idx => $addon) {
                $price = (int) ($addon['price'] ?? 0);
                if ($price <= 0) {
                    continue;
                }
                $itemDetails[] = [
                    'id' => 'addon-'.$booking->id.'-'.$idx,
                    'price' => $price,
                    'quantity' => 1,
                    'name' => (string) ($addon['label'] ?? 'Addon'),
                ];
            }
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
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
            'status' => 'PENDING',
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

    /** Terima webhook Midtrans dan sinkron status payment + booking. */
    public function webhook(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');

        $payment = Payment::where('order_id', $orderId)->firstOrFail();
        $this->applyStatus($payment, $transactionStatus);

        Log::info('Midtrans webhook handled', ['order_id' => $orderId, 'status' => $transactionStatus]);

        return response()->json(['message' => 'ok']);
    }

    /**
     * Fallback konfirmasi dari callback frontend (server-side verify ke Midtrans).
     */
    public function confirm(Request $request, Booking $booking)
    {
        $this->authorizeBooking($request, $booking);

        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', 'PENDING')
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Tidak ada transaksi pending.',
                'booking_status' => $booking->status,
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
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

    /** Pastikan hanya owner booking atau admin yang boleh buat pembayaran. */
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

        // Mapping status Midtrans -> status payment internal.
        $statusMap = [
            'settlement' => 'PAID',
            'capture' => 'PAID',
            'pending' => 'PENDING',
            'expire' => 'EXPIRED',
            'cancel' => 'FAILED',
            'failure' => 'FAILED',
            'deny' => 'FAILED',
        ];

        $newStatus = $statusMap[$transactionStatus] ?? 'PENDING';

        $payment->update([
            'transaction_status' => $transactionStatus,
            'status' => $newStatus,
            'paid_at' => $newStatus === 'PAID' ? Carbon::now() : null,
        ]);

        if ($newStatus === 'PAID') {
            $booking->status = $payment->type === 'DP' ? 'DP_PAID' : 'PAID';
            $booking->save();

            if ($previousStatus !== 'PAID') {
                $booking->client?->notify(new PaymentConfirmedNotification($payment->id));
            }
        } elseif (in_array($newStatus, ['EXPIRED', 'FAILED'], true)) {
            $booking->status = 'WAITING_PAYMENT';
            $booking->save();
        }
    }
}
