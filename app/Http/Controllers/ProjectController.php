<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Detail project produksi: galeri, seleksi, revisi, dan jadwal.
 */
class ProjectController extends Controller
{
    /** Tampilkan galeri project + assets + seleksi */
    public function show(Project $project)
    {
        $user = Auth::user();

        // akses: owner client, admin, photog, editor
        if ($user->role === \App\Enums\Role::CLIENT && $project->booking->client_id !== $user->id) {
            abort(403);
        }

        $project->load([
            'booking.package',
            'mediaAssets',
            'selections',
            'revisionPins',
            'schedule'
        ]);

        return view('projects.show', compact('project'));
    }
}
