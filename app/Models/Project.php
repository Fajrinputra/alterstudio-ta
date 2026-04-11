<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representasi workflow pasca-booking (jadwal, upload, seleksi, final).
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
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Semua aset media (raw/final) di project ini. */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    /** Foto yang dipilih client untuk diedit. */
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
}
