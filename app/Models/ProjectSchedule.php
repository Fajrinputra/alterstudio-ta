<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function users(): HasMany
    {
        return $this->hasMany(ProjectScheduleUser::class);
    }

    public function photographerAssignment(): HasOne
    {
        return $this->hasOne(ProjectScheduleUser::class)->where('role', Role::PHOTOGRAPHER->value);
    }

    public function editorAssignment(): HasOne
    {
        return $this->hasOne(ProjectScheduleUser::class)->where('role', Role::EDITOR->value);
    }

    public function getPhotographerIdAttribute(): ?int
    {
        return $this->photographerAssignment?->user_id;
    }

    public function getEditorIdAttribute(): ?int
    {
        return $this->editorAssignment?->user_id;
    }

    public function getPhotographerAttribute(): ?User
    {
        return $this->photographerAssignment?->user;
    }

    public function getEditorAttribute(): ?User
    {
        return $this->editorAssignment?->user;
    }
}
