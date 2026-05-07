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
        'is_active',
        'city',
        'phone',
        'email',
        'latitude',
        'longitude',
        'facilities',
        'photo_gallery',
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

    public function getPhotoGalleryAttribute($value): array
    {
        return collect(is_array($value) ? $value : (json_decode((string) $value, true) ?: []))
            ->map(function ($item) {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return $item['path'] ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getPhotoPathAttribute($value): ?string
    {
        return collect($this->photo_gallery)->first();
    }
}
