<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * File media project (mis. RAW dari fotografer / FINAL dari editor).
 */
class MediaAsset extends Model
{
    use HasFactory;

    public const TYPE_RAW = 'RAW';
    public const TYPE_FINAL = 'FINAL';

    public const TYPES = [
        self::TYPE_RAW,
        self::TYPE_FINAL,
    ];

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

}
