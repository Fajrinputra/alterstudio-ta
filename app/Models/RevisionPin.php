<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan revisi pada titik koordinat tertentu di gambar.
 */
class RevisionPin extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'media_asset_id',
        'client_id',
        'x',
        'y',
        'comment',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'resolved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
