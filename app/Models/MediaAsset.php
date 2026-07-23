<?php

namespace App\Models;

use App\Models\Concerns\HasPublicCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Metadata file lama; workflow baru menyimpan link Drive langsung di project.
 */
class MediaAsset extends Model
{
    use HasFactory;
    use HasPublicCode;

    /** Primary key adalah kode media, bukan auto-increment integer. */
    protected $primaryKey = 'media_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public const TYPE_RAW = 'RAW';
    public const TYPE_FINAL = 'FINAL';

    public const TYPES = [
        self::TYPE_RAW,
        self::TYPE_FINAL,
    ];

    protected $fillable = [
        'media_code',
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

    protected function publicCodeColumn(): string
    {
        return 'media_code';
    }

    protected function publicCodePrefix(): string
    {
        return 'MEDIA';
    }
}
