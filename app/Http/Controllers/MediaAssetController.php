<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\MediaAsset;
use App\Models\Project;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\RawPhotosUploadedNotification;
use Illuminate\Http\Request;

/**
 * Mengelola workflow pasca-produksi berbasis Google Drive.
 */
class MediaAssetController extends Controller
{
    /**
     * Menyimpan link Drive foto mentah dari fotografer dan tanda hasil final dari editor.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', MediaAsset::TYPES)],
        ]);

        return $validated['type'] === MediaAsset::TYPE_RAW
            ? $this->storeRawDriveLink($request, $project)
            : $this->storeFinalDelivery($request, $project);
    }

    protected function storeRawDriveLink(Request $request, Project $project)
    {
        $this->authorizeRawSubmission($request, $project);

        if ($response = $this->ensurePostProductionAllowed($request, $project)) {
            return $response;
        }

        if ($project->hasRawDriveLink()) {
            return $this->respondBack($request, 'Link Drive foto mentah sudah tersimpan dan tidak dapat diunggah ulang.', 422);
        }

        if (! $project->canStartPostProduction()) {
            return $this->respondBack($request, 'Link Drive foto mentah hanya dapat dikirim saat project berstatus Terjadwal.', 422);
        }

        $data = $request->validate([
            'raw_drive_url' => ['required', 'url', 'max:2048'],
        ]);

        $project->update([
            'raw_drive_url' => $data['raw_drive_url'],
            'raw_drive_uploaded_by' => $request->user()->id,
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);

        $project->booking?->client?->notify(new RawPhotosUploadedNotification($project->id));

        return $this->respondSuccess($request, $project->fresh(), 'Link Drive foto mentah berhasil disimpan. Klien telah diberi notifikasi.');
    }

    protected function storeFinalDelivery(Request $request, Project $project)
    {
        $this->authorizeFinalSubmission($request, $project);

        if ($response = $this->ensurePostProductionAllowed($request, $project)) {
            return $response;
        }

        if (! $project->hasEditRequest()) {
            return $this->respondBack($request, 'Permintaan edit dari klien belum tersedia.', 422);
        }

        if ($project->hasFinalDelivery()) {
            return $this->respondBack($request, 'Hasil final sudah ditandai tersedia.', 422);
        }

        $data = $request->validate([
            'final_drive_url' => ['nullable', 'url', 'max:2048'],
            'final_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $driveUrl = $data['final_drive_url'] ?? $project->final_drive_url ?? $project->raw_drive_url;
        if (! $driveUrl) {
            return $this->respondBack($request, 'Link Drive hasil final belum tersedia.', 422);
        }

        $project->update([
            'final_drive_url' => $driveUrl,
            'final_message' => $data['final_message'] ?? null,
            'final_drive_uploaded_by' => $request->user()->id,
            'final_drive_uploaded_at' => now(),
            'status' => Project::STATUS_FINAL,
        ]);

        $project->booking?->client?->notify(new FinalPhotosReadyNotification($project->id));

        return $this->respondSuccess($request, $project->fresh(), 'Hasil final berhasil ditandai tersedia. Klien telah diberi notifikasi.');
    }

    /**
     * Endpoint lama diarahkan ke Drive agar link bookmark lama tetap berguna.
     */
    public function downloadRaw(Project $project)
    {
        $user = request()->user();
        if ($user->role === Role::CLIENT && $project->booking->client_id !== $user->id) {
            abort(403);
        }

        if ($response = $this->ensurePostProductionAllowed(request(), $project)) {
            return $response;
        }

        if (! $project->raw_drive_url) {
            return back()->with('error', 'Link Drive foto mentah belum tersedia.');
        }

        return redirect()->away($project->raw_drive_url);
    }

    protected function ensurePostProductionAllowed(Request $request, Project $project)
    {
        if ($message = $project->productionBlockMessage()) {
            return $this->respondBack($request, $message, 422);
        }

        return null;
    }

    protected function authorizeRawSubmission(Request $request, Project $project): void
    {
        $user = $request->user();
        $isCrewOnly = $user->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
            && ! $user->isRole(Role::OWNER, Role::ADMIN, Role::MANAGER, Role::CLIENT);

        if ($isCrewOnly && (! $user->isRole(Role::PHOTOGRAPHER) || (int) $project->photographer_id !== (int) $user->id)) {
            abort(403, 'Anda tidak ditugaskan sebagai fotografer pada project ini.');
        }
    }

    protected function authorizeFinalSubmission(Request $request, Project $project): void
    {
        $user = $request->user();
        $isCrewOnly = $user->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
            && ! $user->isRole(Role::OWNER, Role::ADMIN, Role::MANAGER, Role::CLIENT);

        if ($isCrewOnly && (! $user->isRole(Role::EDITOR) || (int) $project->editor_id !== (int) $user->id)) {
            abort(403, 'Anda tidak ditugaskan sebagai editor pada project ini.');
        }
    }

    protected function respondBack(Request $request, string $message, int $status = 200)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message], $status)
            : back()->with('error', $message);
    }

    protected function respondSuccess(Request $request, Project $project, string $message)
    {
        return $request->wantsJson()
            ? response()->json([
                'id' => $project->id,
                'status' => $project->status,
                'raw_drive_url' => $project->raw_drive_url,
                'final_drive_url' => $project->final_drive_url,
                'message' => $message,
            ])
            : back()->with('success', $message);
    }
}
