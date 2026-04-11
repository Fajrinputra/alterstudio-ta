<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ruangan aktif/nonaktif di dalam satu cabang studio.
 */
class StudioRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'studio_location_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Lokasi induk ruangan. */
    public function location(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class, 'studio_location_id');
    }

    /** Semua booking yang menggunakan ruangan ini. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'studio_room_id');
    }
}
