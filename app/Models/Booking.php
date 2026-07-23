<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Booking — Entitas pemesanan utama sistem AlterStudio.
 *
 * Siklus hidup (status) sebuah booking:
 *   WAITING_PAYMENT (tanpa confirmed_at) → "Diajukan"
 *   WAITING_PAYMENT (dengan confirmed_at) → "Dikonfirmasi, menunggu pembayaran"
 *   DP_PAID                              → "DP sudah dibayar"
 *   PAID                                 → "Lunas"
 *   CANCELLED                            → "Dibatalkan"
 *
 * Relasi kunci:
 * - client   : pengguna yang melakukan pemesanan
 * - package  : paket layanan yang dipilih (dengan soft-delete)
 * - payments : riwayat semua transaksi pembayaran
 * - project  : workflow produksi pasca-booking
 * - studioLocation/studioRoom : lokasi dan ruangan yang dipilih
 *
 * Perubahan skema terakhir (migrasi 2026_07_22):
 * - FK berbasis kode string (location_code, room_code)
 * - Kolom integer studio_location_id/studio_room_id dihapus
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
        'confirmed_at',
        'payment_started_at',
        'payment_type',
        'addon_total',
        'total_price',
        'studio_location_code',
        'studio_room_code',
        'selected_addons',
    ];

    protected $casts = [
        'booking_date'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'payment_started_at' => 'datetime',
        'addon_total'        => 'integer',
        'total_price'        => 'integer',
        'selected_addons'    => 'array',
    ];

    /**
     * Daftar konstanta status pemesanan.
     * WAITING_PAYMENT dipakai untuk 2 kondisi berbeda:
     * - confirmed_at = null  → baru diajukan, belum dikonfirmasi admin
     * - confirmed_at != null → sudah dikonfirmasi, menunggu klien bayar
     */
    public const STATUS_WAITING_PAYMENT = 'WAITING_PAYMENT';
    public const STATUS_DP_PAID = 'DP_PAID';         // Uang muka 10% sudah dibayar.
    public const STATUS_PAID = 'PAID';               // Lunas 100%.
    public const STATUS_CANCELLED = 'CANCELLED';    // Dibatalkan.

    /** Array semua status untuk validasi input. */
    public const STATUSES = [
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_DP_PAID,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_TYPE_DP   = 'DP';     // Klien memilih bayar uang muka dulu.
    public const PAYMENT_TYPE_FULL = 'FULL';   // Klien memilih bayar lunas sekaligus.
    public const DOWN_PAYMENT_PERCENTAGE = 10; // Persentase uang muka (10% dari total).
    public const EXTRA_TIME_ADDON_MINUTES = 10;// Durasi tambahan per add-on "tambah waktu".

    /** Pengguna (klien) pemilik pemesanan ini. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Paket layanan yang dipesan.
     * withTrashed() memastikan paket yang sudah di-soft-delete
     * tetap bisa dimuat untuk keperluan histori transaksi.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id')->withTrashed();
    }

    /** Semua transaksi pembayaran yang terkait dengan booking ini. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Project workflow produksi (dibuat otomatis saat booking pertama kali disimpan).
     * Relasi HasOne karena satu booking hanya punya satu project.
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /**
     * Lokasi/cabang studio yang dipilih saat booking.
     * Foreign key adalah string (location_code), bukan integer ID.
     */
    public function studioLocation(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class, 'studio_location_code', 'location_code');
    }

    /**
     * Ruangan spesifik dalam cabang yang dipilih saat booking.
     * Foreign key adalah string (room_code), bukan integer ID.
     */
    public function studioRoom(): BelongsTo
    {
        return $this->belongsTo(StudioRoom::class, 'studio_room_code', 'room_code');
    }

    public function getSelectedAddonsAttribute($value): array
    {
        $items = is_array($value) ? $value : ($value ? (json_decode((string) $value, true) ?: []) : []);

        return collect($items)
            ->map(function ($addon) {
                if (! is_array($addon)) {
                    return null;
                }

                $label = trim((string) ($addon['label'] ?? ''));
                if ($label === '') {
                    return null;
                }

                return [
                    'label'    => $label,
                    'price'    => (int) ($addon['price'] ?? 0),
                    'unit'     => trim((string) ($addon['unit'] ?? '')),
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

    /**
     * Mengembalikan batas waktu pembayaran (30 menit sejak payment_started_at).
     * Mengembalikan null jika timer pembayaran belum dimulai.
     */
    public function paymentDeadlineAt(): ?Carbon
    {
        return $this->payment_started_at?->copy()->addMinutes(30);
    }

    /**
     * Mengecek apakah window pembayaran sudah kadaluarsa.
     * Window dihitung 30 menit sejak payment_started_at pertama kali diisi.
     */
    public function isPaymentWindowExpired(): bool
    {
        return $this->status === self::STATUS_WAITING_PAYMENT
            && $this->payment_started_at !== null
            && $this->paymentDeadlineAt()?->isPast() === true;
    }

    /**
     * Mengecek apakah booking baru diajukan (belum dikonfirmasi admin/manajer).
     * Kondisi: status WAITING_PAYMENT DAN confirmed_at masih null.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_WAITING_PAYMENT
            && $this->confirmed_at === null;
    }

    /**
     * Mengecek apakah booking sudah dikonfirmasi dan menunggu pembayaran klien.
     * Kondisi: status WAITING_PAYMENT DAN confirmed_at sudah terisi.
     */
    public function isConfirmedAwaitingPayment(): bool
    {
        return $this->status === self::STATUS_WAITING_PAYMENT
            && $this->confirmed_at !== null;
    }

    /** Mengecek apakah timer 30 menit pembayaran sudah mulai berjalan. */
    public function hasPaymentWindowStarted(): bool
    {
        return $this->payment_started_at !== null;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isSubmitted()                      => 'Diajukan',
            $this->isConfirmedAwaitingPayment()       => 'Dikonfirmasi',
            $this->status === self::STATUS_DP_PAID    => 'DP Dibayar',
            $this->status === self::STATUS_PAID       => 'Lunas',
            $this->status === self::STATUS_CANCELLED  => 'Dibatalkan',
            default                                   => $this->status,
        };
    }

    /**
     * Menghitung total pembayaran yang sudah diterima (status PAID).
     * Menggunakan relasi yang sudah di-load jika ada (menghindari query tambahan).
     */
    public function paidAmount(): int
    {
        if ($this->relationLoaded('payments')) {
            return (int) $this->payments
                ->where('status', Payment::STATUS_PAID)
                ->sum('amount');
        }

        return (int) $this->payments()
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');
    }

    /** Menghitung sisa pembayaran yang masih harus dibayar klien. */
    public function remainingAmount(): int
    {
        return max(0, (int) $this->total_price - $this->paidAmount());
    }

    /** Durasi dasar sesi foto berdasarkan paket yang dipilih (default 60 menit). */
    public function baseDurationMinutes(): int
    {
        return max(1, (int) ($this->package?->duration_minutes ?? 60));
    }

    /** Durasi tambahan dari semua add-on "tambah waktu" yang dipilih. */
    public function extraDurationMinutes(): int
    {
        return self::extraDurationMinutesFromAddons($this->selected_addons);
    }

    public static function extraDurationMinutesFromAddons(array $addons): int
    {
        return collect($addons)
            ->filter(fn ($addon) => is_array($addon) && self::isExtraTimeAddon((string) ($addon['label'] ?? '')))
            ->sum(fn ($addon) => max(1, (int) ($addon['quantity'] ?? 1)) * self::EXTRA_TIME_ADDON_MINUTES);
    }

    public static function isExtraTimeAddon(string $label): bool
    {
        return str_contains(strtolower($label), 'tambah waktu');
    }

    public function effectiveDurationMinutes(): int
    {
        return $this->baseDurationMinutes() + $this->extraDurationMinutes();
    }

    public function nextPaymentType(): string
    {
        return $this->status === self::STATUS_DP_PAID
            ? self::PAYMENT_TYPE_FULL
            : $this->payment_type;
    }

    public function downPaymentAmount(): int
    {
        $totalPrice = max(0, (int) $this->total_price);

        return intdiv(($totalPrice * self::DOWN_PAYMENT_PERCENTAGE) + 99, 100);
    }

    public function nextPayableAmount(): int
    {
        if ($this->status === self::STATUS_DP_PAID) {
            return $this->remainingAmount();
        }

        return $this->payment_type === self::PAYMENT_TYPE_DP
            ? $this->downPaymentAmount()
            : (int) $this->total_price;
    }

    public function isAwaitingSettlement(): bool
    {
        return $this->status === self::STATUS_DP_PAID && $this->remainingAmount() > 0;
    }
}
