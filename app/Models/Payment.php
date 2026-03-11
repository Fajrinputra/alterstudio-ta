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
