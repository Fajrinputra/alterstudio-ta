<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\LandingHeroSlide;
use App\Models\StudioLocation;
use Illuminate\Support\Facades\Schema;

/**
 * Menyusun data landing page publik (kategori, paket, cabang, hero).
 */
class LandingController extends Controller
{
    public function __invoke()
    {
        // Ambil kategori + paket aktif dan hitung jumlah booking tiap paket.
        $categories = ServiceCategory::with(['packages' => function ($q) {
            $q->where('is_active', true)
                ->withCount('bookings')
                ->orderBy('price')
                ->orderBy('name');
        }])
            ->orderBy('name')
            ->get();

        $mostPopularPackageIds = $categories
            ->map(function ($category) {
                // Simpan 1 paket terpopuler per kategori untuk badge "diminati".
                return optional(
                    $category->packages
                        ->sortByDesc('bookings_count')
                        ->first()
                )->id;
            })
            ->filter()
            ->values()
            ->all();

        $locations = StudioLocation::where('is_active', true)->orderBy('name')->get();

        $heroSlides = Schema::hasTable('landing_hero_slides')
            ? LandingHeroSlide::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        return view('welcome', compact('categories', 'locations', 'mostPopularPackageIds', 'heroSlides'));
    }
}
