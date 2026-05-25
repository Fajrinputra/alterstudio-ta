<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectScheduleUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_schedule_id',
        'user_id',
        'role',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProjectSchedule::class, 'project_schedule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
