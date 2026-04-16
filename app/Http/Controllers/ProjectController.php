<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Detail project produksi: galeri, seleksi, dan jadwal.
 */
class ProjectController extends Controller
{
    /** Menampilkan detail project, galeri media, dan pilihan foto klien. */
    public function show(Project $project)
    {
        $user = Auth::user();

        // Akses hanya untuk pemilik project atau kru/admin yang terlibat.
        if ($user->role === \App\Enums\Role::CLIENT && $project->booking->client_id !== $user->id) {
            abort(403);
        }

        $project->load([
            'booking.package',
            'booking.studioLocation',
            'booking.studioRoom',
            'mediaAssets',
            'selections',
            'photographer',
            'editor',
        ]);

        return view('projects.show', compact('project'));
    }
}
