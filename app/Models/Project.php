<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    ];

    /** Status workflow project. */
    public const STATUSES = ['DRAFT','SCHEDULED','SHOOT_DONE','EDITING','REVIEW','FINAL'];

    protected $casts = [
        'selections_locked' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Relasi jadwal kru (fotografer/editor). */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
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

    /** Catatan revisi berbasis pin posisi gambar. */
    public function revisionPins(): HasMany
    {
        return $this->hasMany(RevisionPin::class);
    }
}
