<?php

namespace App\Models;

use App\Models\Concerns\HasPublicCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Menyimpan pilihan foto client yang akan diproses editor.
 *
 * Primary key: selection_code (varchar 50, format SEL-XXXXXXXX)
 * FK ke media_assets memakai media_code (bukan integer media_asset_id).
 */
class PhotoSelection extends Model
{
    use HasFactory;
    use HasPublicCode;

    /** Primary key adalah kode seleksi, bukan auto-increment integer. */
    protected $primaryKey = 'selection_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'selection_code',
        'project_id',
        'client_id',
        'media_code',
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

    /** Relasi ke aset media via media_code (PK baru media_assets). */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_code', 'media_code');
    }

    protected function publicCodeColumn(): string
    {
        return 'selection_code';
    }

    protected function publicCodePrefix(): string
    {
        return 'SEL';
    }
}
