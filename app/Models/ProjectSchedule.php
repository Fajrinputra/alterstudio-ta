<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSchedule extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_LOCKED = 'LOCKED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'project_id',
        'booking_id',
        'studio_location_id',
        'studio_room_id',
        'scheduled_by',
        'photographer_id',
        'editor_id',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function studioLocation(): BelongsTo
    {
        return $this->belongsTo(StudioLocation::class);
    }

    public function studioRoom(): BelongsTo
    {
        return $this->belongsTo(StudioRoom::class);
    }

    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
