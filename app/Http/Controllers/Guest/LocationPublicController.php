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
        $studioLocation->load(['rooms']);
        return view('locations.show', [
            'location' => $studioLocation,
            'photos' => $studioLocation->photo_gallery,
        ]);
    }
}
