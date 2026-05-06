<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representasi workflow pasca-booking: jadwal, link Drive, permintaan edit, final.
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'status',
        'selections_locked',
        'photographer_id',
        'editor_id',
        'start_at',
        'end_at',
        'raw_drive_url',
        'raw_drive_uploaded_by',
        'raw_drive_uploaded_at',
        'edit_photo_codes',
        'edit_request_note',
        'edit_requested_at',
        'final_drive_url',
        'final_message',
        'final_drive_uploaded_by',
        'final_drive_uploaded_at',
    ];

    /** Status workflow project. */
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_SHOOT_DONE = 'SHOOT_DONE';
    public const STATUS_EDITING = 'EDITING';
    public const STATUS_FINAL = 'FINAL';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_SHOOT_DONE,
        self::STATUS_EDITING,
        self::STATUS_FINAL,
    ];

    protected $casts = [
        'selections_locked' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'raw_drive_uploaded_at' => 'datetime',
        'edit_requested_at' => 'datetime',
        'final_drive_uploaded_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Metadata file lama, dipertahankan untuk kompatibilitas data historis. */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    /** Pilihan foto lama, dipertahankan untuk kompatibilitas data historis. */
    public function selections(): HasMany
    {
        return $this->hasMany(PhotoSelection::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function rawDriveUploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raw_drive_uploaded_by');
    }

    public function finalDriveUploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_drive_uploaded_by');
    }

    /**
     * Akses kompatibilitas agar view lama tetap bisa memanggil $project->schedule.
     */
    public function getScheduleAttribute(): ?object
    {
        if (! $this->start_at && ! $this->end_at && ! $this->photographer_id && ! $this->editor_id) {
            return null;
        }

        return (object) [
            'project_id' => $this->id,
            'photographer_id' => $this->photographer_id,
            'editor_id' => $this->editor_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'photographer' => $this->photographer,
            'editor' => $this->editor,
            'location' => $this->booking?->location,
        ];
    }

    public function hasSchedule(): bool
    {
        return $this->start_at !== null && $this->end_at !== null;
    }

    public function hasRawDriveLink(): bool
    {
        return filled($this->raw_drive_url);
    }

    public function hasEditRequest(): bool
    {
        return $this->edit_requested_at !== null;
    }

    public function hasFinalDelivery(): bool
    {
        return $this->final_drive_uploaded_at !== null;
    }

    public function hasPostProductionActivity(): bool
    {
        return $this->hasRawDriveLink()
            || $this->hasEditRequest()
            || $this->hasFinalDelivery()
            || $this->mediaAssets()->exists();
    }

    public function bookingAllowsProduction(): bool
    {
        return $this->booking?->status === Booking::STATUS_PAID;
    }

    public function bookingAllowsScheduling(): bool
    {
        return in_array($this->booking?->status, [Booking::STATUS_DP_PAID, Booking::STATUS_PAID], true);
    }

    public function canContinueProduction(): bool
    {
        return $this->productionBlockMessage() === null;
    }

    public function canStartPostProduction(): bool
    {
        return $this->canContinueProduction()
            && $this->status === self::STATUS_SCHEDULED;
    }

    public function productionBlockMessage(): ?string
    {
        if ($this->booking?->status === Booking::STATUS_CANCELLED) {
            return 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.';
        }

        if (! $this->bookingAllowsProduction()) {
            return 'Proses pasca-produksi hanya dapat dilanjutkan setelah pembayaran lunas.';
        }

        if (! $this->hasSchedule() || $this->status === self::STATUS_DRAFT) {
            return 'Proses pasca-produksi hanya dapat dilanjutkan setelah admin menjadwalkan project.';
        }

        return null;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Belum Dijadwalkan',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_SHOOT_DONE => 'Link Foto Mentah Tersedia',
            self::STATUS_EDITING => 'Permintaan Edit Dikirim',
            self::STATUS_FINAL => 'Hasil Final Siap',
            default => $this->status,
        };
    }
}

