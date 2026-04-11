<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Menyimpan transaksi pembayaran (DP/FULL) per booking.
 */
class Payment extends Model
{
    use HasFactory;

    public const TYPE_DP = Booking::PAYMENT_TYPE_DP;
    public const TYPE_FULL = Booking::PAYMENT_TYPE_FULL;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_FAILED = 'FAILED';

    protected $fillable = [
        'booking_id',
        'type',
        'amount',
        'status',
        'reference',
        'order_id',
        'snap_token',
        'transaction_status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    /** Relasi pembayaran ke booking. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
