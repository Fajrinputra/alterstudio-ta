<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * File media project (mis. RAW dari fotografer / FINAL dari editor).
 */
class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'type',
        'path',
        'uploaded_by',
        'version',
        'expires_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Pin revisi yang ditempel di asset ini. */
    public function revisionPins(): HasMany
    {
        return $this->hasMany(RevisionPin::class);
    }
}
