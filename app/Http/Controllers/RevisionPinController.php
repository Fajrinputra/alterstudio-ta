<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Models\Project;
use App\Models\RevisionPin;
use Illuminate\Http\Request;

/**
 * Endpoint pin revisi pada gambar project.
 */
class RevisionPinController extends Controller
{
    /** Client menambah pin revisi di aset miliknya. */
    public function store(Request $request, Project $project)
    {
        $user = $request->user();
        if ($user->id !== $project->booking->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'media_asset_id' => ['required', 'exists:media_assets,id'],
            'x' => ['required', 'numeric'],
            'y' => ['required', 'numeric'],
            'comment' => ['required', 'string'],
        ]);

        $asset = MediaAsset::where('id', $validated['media_asset_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        $pin = RevisionPin::create([
            'project_id' => $project->id,
            'media_asset_id' => $asset->id,
            'client_id' => $user->id,
            'x' => $validated['x'],
            'y' => $validated['y'],
            'comment' => $validated['comment'],
            'status' => 'OPEN',
        ]);

        return response()->json($pin, 201);
    }

    /** Editor/Admin menandai pin revisi sebagai selesai. */
    public function resolve(Request $request, RevisionPin $revisionPin)
    {
        $user = $request->user();
        if (!in_array($user->role, [\App\Enums\Role::EDITOR, \App\Enums\Role::ADMIN], true)) {
            abort(403);
        }

        $revisionPin->update([
            'status' => 'RESOLVED',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
        ]);

        return response()->json($revisionPin);
    }
}
