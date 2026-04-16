<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'studio_location_id',
        'holiday_date',
        'name',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_active' => 'boolean',
    ];

    /** Cabang studio yang memiliki hari libur ini. */
    public function studioLocation(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class);
    }
}
