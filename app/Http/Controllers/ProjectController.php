<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Detail project produksi: jadwal, link Drive, permintaan edit, final.
 */
class ProjectController extends Controller
{
    /** Menampilkan detail project dan progres pasca-produksi berbasis Drive. */
    public function show(Project $project)
    {
        $user = Auth::user();
        $isCrewOnly = $user->isRole(\App\Enums\Role::PHOTOGRAPHER, \App\Enums\Role::EDITOR)
            && ! $user->isRole(\App\Enums\Role::OWNER, \App\Enums\Role::ADMIN, \App\Enums\Role::MANAGER, \App\Enums\Role::CLIENT);

        // Akses hanya untuk pemilik project atau kru/admin yang terlibat.
        if ($user->role === \App\Enums\Role::CLIENT && $project->booking->client_id !== $user->id) {
            abort(403);
        }

        if ($isCrewOnly && ! in_array($user->id, [$project->photographer_id, $project->editor_id], true)) {
            abort(403);
        }

        $project->load([
            'booking.package',
            'booking.studioLocation',
            'booking.studioRoom',
            'photographer',
            'editor',
        ]);

        return view('projects.show', compact('project'));
    }
}
