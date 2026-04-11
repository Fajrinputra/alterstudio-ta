<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Notifications\EditRequestSubmittedNotification;
use Illuminate\Http\Request;

/**
 * Kelola seleksi foto client sebelum masuk proses editing.
 */
class PhotoSelectionController extends Controller
{
    /** Client memilih foto (max 5) hanya untuk project miliknya. */
    public function store(Request $request, Project $project)
    {
        $user = $request->user();
        if ($user->id !== $project->booking->client_id) {
            abort(403);
        }

        if ($project->selections_locked) {
            return back()->with('error', 'Pilihan sudah dikirim ke editor dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'media_asset_id' => ['required', 'exists:media_assets,id'],
        ]);

        $asset = MediaAsset::where('id', $validated['media_asset_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        // Toggle: jika sudah dipilih, hapus. Jika belum, tambahkan (maks 5).
        $existing = PhotoSelection::where('project_id', $project->id)
            ->where('client_id', $user->id)
            ->where('media_asset_id', $asset->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Pilihan dibatalkan.';
        } else {
            $currentCount = PhotoSelection::where('project_id', $project->id)->count();
            if ($currentCount >= 5) {
                return back()->with('error', 'Maksimum 5 foto dapat dipilih.');
            }

            $selection = PhotoSelection::create([
                'project_id' => $project->id,
                'client_id' => $user->id,
                'media_asset_id' => $asset->id,
            ]);
            $message = 'Foto ditandai untuk diedit. Tekan Kirim ke Editor jika sudah selesai memilih.';
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 200);
        }

        return back()->with('success', $message);
    }

    /** Kunci pilihan dan tandai permintaan edit dikirim. */
    public function finalize(Project $project)
    {
        $user = request()->user();
        if ($user->id !== $project->booking->client_id) {
            abort(403);
        }

        if ($project->selections_locked) {
            return back()->with('success', 'Permintaan edit sudah dikirim.');
        }

        $count = $project->selections()->count();
        if ($count === 0) {
            return back()->with('error', 'Pilih minimal 1 foto sebelum mengirim.');
        }

        $project->update([
            'selections_locked' => true,
            'status' => Project::STATUS_EDITING,
        ]);

        // Saat finalize, editor terkait diberi notifikasi job baru.
        $editor = $project->editor;
        if ($editor) {
            $editor->notify(new EditRequestSubmittedNotification($project->id));
        }

        return back()->with('success', 'Permintaan edit dikirim ke editor. Pilihan terkunci.');
    }
}
