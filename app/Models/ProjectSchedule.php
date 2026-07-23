<?php

namespace App\Models;

use App\Models\Concerns\HasPublicCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jadwal sesi pemotretan per project.
 *
 * Primary key: schedule_code (varchar 50, format SCH-XXXXXXXX)
 * FK ke lokasi/ruangan memakai varchar code, bukan integer id.
 */
class ProjectSchedule extends Model
{
    use HasFactory;
    use HasPublicCode;

    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_LOCKED    = 'LOCKED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /** Primary key adalah kode jadwal, bukan auto-increment integer. */
    protected $primaryKey = 'schedule_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'schedule_code',
        'project_id',
        'booking_id',
        'studio_location_code',
        'studio_room_code',
        'scheduled_by',
        'photographer_id',
        'editor_id',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
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
        return $this->belongsTo(StudioLocation::class, 'studio_location_code', 'location_code');
    }

    public function studioRoom(): BelongsTo
    {
        return $this->belongsTo(StudioRoom::class, 'studio_room_code', 'room_code');
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

    protected function publicCodeColumn(): string
    {
        return 'schedule_code';
    }

    protected function publicCodePrefix(): string
    {
        return 'SCH';
    }
}
