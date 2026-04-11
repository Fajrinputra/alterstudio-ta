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
        'duration_minutes',
        'description',
        'terms',
        'cover_image',
        'is_active',
        'features',
        'addons',
        'gallery',
    ];

    protected $casts = [
        'terms' => 'string',
        'price' => 'integer',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'features' => 'array',
        'addons' => 'array',
        'gallery' => 'array',
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

    public function getOverviewImageAttribute($value): ?string
    {
        if (is_string($this->cover_image) && $this->cover_image !== '') {
            return $this->cover_image;
        }

        $gallery = collect($this->gallery ?? [])->filter()->values();

        return $gallery->first();
    }

    public function getFeaturesAttribute($value): array
    {
        return collect(is_array($value) ? $value : (json_decode((string) $value, true) ?: []))
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->values()
            ->all();
    }

    public function getAddonsAttribute($value): array
    {
        return collect(is_array($value) ? $value : (json_decode((string) $value, true) ?: []))
            ->map(function ($addon) {
                if (! is_array($addon)) {
                    return null;
                }

                $label = trim((string) ($addon['label'] ?? ''));
                if ($label === '') {
                    return null;
                }

                 [$normalizedLabel, $normalizedPrice] = $this->parseAddonLabelAndPrice(
                    $label,
                    (int) ($addon['price'] ?? 0)
                );

                return [
                    'label' => $normalizedLabel,
                    'price' => $normalizedPrice,
                    'unit' => trim((string) ($addon['unit'] ?? '')),
                    'is_active' => (bool) ($addon['is_active'] ?? true),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getGalleryAttribute($value): array
    {
        return collect(is_array($value) ? $value : (json_decode((string) $value, true) ?: []))
            ->map(function ($item) {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return $item['path'] ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function parseAddonLabelAndPrice(string $label, int $price): array
    {
        if ($price > 0) {
            return [$label, $price];
        }

        if (preg_match('/(rp)?\s*([0-9][0-9\.,]*)\s*(k|rb|ribu)?/i', $label, $matches, PREG_OFFSET_CAPTURE)) {
            $fullMatch = $matches[0][0] ?? '';
            $offset = $matches[0][1] ?? null;
            $nominalRaw = $matches[2][0] ?? '0';
            $suffix = strtolower($matches[3][0] ?? '');

            $nominal = (float) preg_replace('/[^0-9]/', '', $nominalRaw);
            if (in_array($suffix, ['k', 'rb', 'ribu'], true)) {
                $nominal *= 1000;
            }

            $cleanLabel = $label;
            if ($offset !== null) {
                $cleanLabel = trim(substr($label, 0, $offset).substr($label, $offset + strlen($fullMatch)));
            }

            $cleanLabel = preg_replace('/\s*\/\s*[A-Za-z0-9]+$/', '', $cleanLabel ?? '');
            $cleanLabel = trim((string) preg_replace('/[\-|:,\/]+$/', '', $cleanLabel));
            $cleanLabel = preg_replace('/\s+/', ' ', $cleanLabel ?? '');

            return [$cleanLabel !== '' ? $cleanLabel : $label, (int) $nominal];
        }

        return [$label, $price];
    }
}
