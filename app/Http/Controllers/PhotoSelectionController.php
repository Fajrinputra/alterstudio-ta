<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Notifications\EditRequestSubmittedNotification;
use App\Support\DeferredNotification;
use Illuminate\Http\Request;

/**
 * Mengelola permintaan edit klien berbasis kode foto.
 */
class PhotoSelectionController extends Controller
{
    /**
     * Klien mengirim kode foto dan deskripsi edit setelah link Drive RAW tersedia.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorizeClient($request, $project);

        if ($message = $project->productionBlockMessage()) {
            return $this->respondBack($request, $message, 422);
        }

        if (! $project->hasRawDriveLink()) {
            return $this->respondBack($request, 'Link Drive foto mentah belum tersedia.', 422);
        }

        if ($project->isRawDriveExpired()) {
            return $this->respondBack($request, 'Masa akses link Drive foto mentah sudah kedaluwarsa setelah 3 hari.', 422);
        }

        if ($project->hasEditRequest()) {
            return $this->respondBack($request, 'Permintaan edit sudah dikirim dan tidak dapat diubah.', 422);
        }

        $data = $request->validate([
            'edit_photo_codes' => ['required', 'string', 'max:2000'],
            'edit_request_note' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->countPhotoCodes($data['edit_photo_codes']) > 10) {
            return $this->respondBack($request, 'Maksimal 10 foto dapat diajukan untuk diedit.', 422);
        }

        $project->update([
            'edit_photo_codes' => $data['edit_photo_codes'],
            'edit_request_note' => $data['edit_request_note'],
            'edit_requested_at' => now(),
            'selections_locked' => true,
            'status' => Project::STATUS_EDITING,
        ]);

        DeferredNotification::to($project->editor, new EditRequestSubmittedNotification($project->id));

        return $this->respondSuccess($request, 'Permintaan edit berhasil dikirim ke editor.');
    }

    protected function authorizeClient(Request $request, Project $project): void
    {
        if ($request->user()->id !== $project->booking->client_id) {
            abort(403);
        }
    }

    protected function countPhotoCodes(string $codes): int
    {
        $items = preg_split('/[\s,;]+/', trim($codes)) ?: [];

        return count(array_filter($items, fn (string $item) => $item !== ''));
    }

    protected function respondBack(Request $request, string $message, int $status = 200)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message], $status)
            : back()->with('error', $message);
    }

    protected function respondSuccess(Request $request, string $message)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message])
            : back()->with('success', $message);
    }
}
