<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\StudioLocation;

/**
 * Halaman detail cabang yang bisa diakses guest.
 */
class LocationPublicController extends Controller
{
    /** Tampilkan detail cabang untuk guest. */
    public function show(StudioLocation $studioLocation)
    {
        $studioLocation->load(['rooms' => fn ($q) => $q->where('is_active', true)->orderBy('name')]);

        // Gabungkan foto galeri lokasi + foto semua ruangan aktif
        $locationPhotos = collect($studioLocation->photo_gallery ?? [])
            ->map(fn ($path) => ['path' => $path, 'label' => $studioLocation->name, 'type' => 'location']);

        $roomPhotos = $studioLocation->rooms
            ->filter(fn ($room) => filled($room->photo_path))
            ->map(fn ($room) => ['path' => $room->photo_path, 'label' => $room->name, 'type' => 'room']);

        $allPhotos = $locationPhotos->merge($roomPhotos)->values();

        return view('locations.show', [
            'location'  => $studioLocation,
            'photos'    => $allPhotos,          // collection of ['path', 'label', 'type']
            'heroPhoto' => $allPhotos->first(), // foto pertama untuk hero section
        ]);
    }
}
