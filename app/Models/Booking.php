<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Entitas pemesanan utama (client memilih paket + jadwal + pembayaran).
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'package_id',
        'booking_date',
        'booking_time',
        'location',
        'notes',
        'status',
        'payment_type',
        'selected_addons',
        'addon_total',
        'total_price',
        'studio_location_id',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'selected_addons' => 'array',
        'addon_total' => 'integer',
        'total_price' => 'integer',
    ];

    /** Status yang dipakai booking. */
    public const STATUSES = ['DRAFT','WAITING_PAYMENT','DP_PAID','PAID','CANCELLED'];

    /** Client pemilik pemesanan. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** Paket layanan yang dipesan (tetap tersedia walau soft-delete). */
    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id')->withTrashed();
    }

    /** Riwayat transaksi pembayaran untuk booking ini. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Satu booking memiliki satu project workflow produksi. */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /** Cabang/studio lokasi yang dipilih saat booking. */
    public function studioLocation(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class, 'studio_location_id');
    }
}
