<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Slot jadwal produksi untuk sebuah project.
 */
class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'photographer_id',
        'editor_id',
        'start_at',
        'end_at',
        'location',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /** Jadwal terkait project. */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /** Editor yang ditugaskan untuk project ini. */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
