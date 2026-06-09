<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Slide hero landing page yang dapat dikelola admin.
 */
class LandingHeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'eyebrow',
        'title',
        'subtitle',
        'image_path',
        'sort_order',
        'is_active',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /** User pengelola slide hero. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setCreatedByAttribute($value): void
    {
        $this->attributes['user_id'] = $value;
    }

    public function setUpdatedByAttribute($value): void
    {
        $this->attributes['user_id'] = $value;
    }
}
