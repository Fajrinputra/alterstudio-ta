<?php

namespace App\Models;

use App\Models\Concerns\HasPublicCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master data cabang/lokasi studio.
 *
 * Primary key: location_code (varchar 50, format LOC-XXXXXXXX)
 */
class StudioLocation extends Model
{
    use HasFactory;
    use HasPublicCode;

    /** Primary key adalah kode lokasi, bukan auto-increment integer. */
    protected $primaryKey = 'location_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'location_code',
        'name',
        'slug',
        'address',
        'description',
        'map_url',
        'is_active',
        'phone',
        'email',
        'photo_gallery',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'photo_gallery' => 'array',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(StudioRoom::class, 'studio_location_code', 'location_code');
    }

    /** Booking yang memilih cabang ini. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'studio_location_code', 'location_code');
    }

    public function getPhotoGalleryAttribute($value): array
    {
        $items = is_array($value) ? $value : ($value ? (json_decode((string) $value, true) ?: []) : []);

        return collect($items)
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

    protected function publicCodeColumn(): string
    {
        return 'location_code';
    }

    protected function publicCodePrefix(): string
    {
        return 'LOC';
    }
}
