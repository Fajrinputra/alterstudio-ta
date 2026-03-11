<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function location()
    {
        return $this->belongsTo(StudioLocation::class, 'studio_location_id');
    }
}
