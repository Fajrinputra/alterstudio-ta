<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Paket layanan yang dijual ke client.
 */
class ServicePackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'max_people',
        'description',
        'features',
        'addons',
        'terms',
        'portfolio_url',
        'cover_image',
        'overview_image',
        'is_active',
        'gallery',
    ];

    protected $casts = [
        'features' => 'array',
        'addons' => 'array',
        'terms' => 'string',
        'price' => 'integer',
        'is_active' => 'boolean',
        'gallery' => 'array',
        'overview_image' => 'string',
    ];

    /** Paket berada dalam satu kategori. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'package_id');
    }
}
