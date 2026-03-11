<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori layanan (Wedding, Family, dsb) yang menaungi paket.
 */
class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /** Kategori memiliki banyak paket. */
    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class, 'category_id');
    }
}
