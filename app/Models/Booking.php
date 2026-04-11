<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

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
        'notes',
        'status',
        'payment_type',
        'addon_total',
        'total_price',
        'studio_location_id',
        'studio_room_id',
        'selected_addons',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'addon_total' => 'integer',
        'total_price' => 'integer',
        'selected_addons' => 'array',
    ];

    /** Status yang dipakai booking. */
    public const STATUS_WAITING_PAYMENT = 'WAITING_PAYMENT';
    public const STATUS_DP_PAID = 'DP_PAID';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUSES = [
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_DP_PAID,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_TYPE_DP = 'DP';
    public const PAYMENT_TYPE_FULL = 'FULL';

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

    /** Ruangan studio spesifik di dalam cabang yang dipilih. */
    public function studioRoom(): BelongsTo
    {
        return $this->belongsTo(StudioRoom::class, 'studio_room_id');
    }

    public function getSelectedAddonsAttribute($value): array
    {
        return collect(is_array($value) ? $value : (json_decode((string) $value, true) ?: []))
            ->map(function ($addon) {
                if (! is_array($addon)) {
                    return null;
                }

                $label = trim((string) ($addon['label'] ?? ''));
                if ($label === '') {
                    return null;
                }

                return [
                    'label' => $label,
                    'price' => (int) ($addon['price'] ?? 0),
                    'unit' => trim((string) ($addon['unit'] ?? '')),
                    'quantity' => max(1, (int) ($addon['quantity'] ?? 1)),
                    'subtotal' => isset($addon['subtotal'])
                        ? max(0, (int) $addon['subtotal'])
                        : ((int) ($addon['price'] ?? 0) * max(1, (int) ($addon['quantity'] ?? 1))),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getLocationAttribute($value): ?string
    {
        $parts = array_filter([
            $this->studioLocation?->name,
            $this->studioRoom?->name,
        ]);

        if (!empty($parts)) {
            return implode(' - ', $parts);
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function paymentDeadlineAt(): ?Carbon
    {
        return $this->created_at?->copy()->addMinutes(30);
    }

    public function isPaymentWindowExpired(): bool
    {
        return $this->status === self::STATUS_WAITING_PAYMENT
            && $this->paymentDeadlineAt()?->isPast() === true;
    }
}
