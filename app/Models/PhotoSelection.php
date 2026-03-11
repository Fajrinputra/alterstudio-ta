<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Menyimpan pilihan foto client yang akan diproses editor.
 */
class PhotoSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'media_asset_id',
        'selected_at',
    ];

    protected $casts = [
        'selected_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }
}
