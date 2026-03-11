<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master data cabang/lokasi studio.
 */
class StudioLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'description',
        'map_url',
        'photo_path',
        'photo_gallery',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'photo_gallery' => 'array',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(StudioRoom::class);
    }

    /** Booking yang memilih cabang ini. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'studio_location_id');
    }
}
