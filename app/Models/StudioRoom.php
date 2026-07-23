<?php

namespace App\Models;

use App\Models\Concerns\HasPublicCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ruangan aktif/nonaktif di dalam satu cabang studio.
 *
 * Primary key: room_code (varchar 50, format ROOM-XXXXXXXX)
 */
class StudioRoom extends Model
{
    use HasFactory;
    use HasPublicCode;

    /** Primary key adalah kode ruangan, bukan auto-increment integer. */
    protected $primaryKey = 'room_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'room_code',
        'studio_location_code',
        'name',
        'description',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Lokasi induk ruangan. */
    public function location(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class, 'studio_location_code', 'location_code');
    }

    /** Semua booking yang menggunakan ruangan ini. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'studio_room_code', 'room_code');
    }

    protected function publicCodeColumn(): string
    {
        return 'room_code';
    }

    protected function publicCodePrefix(): string
    {
        return 'ROOM';
    }
}
