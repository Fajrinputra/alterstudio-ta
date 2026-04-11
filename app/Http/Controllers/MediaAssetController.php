<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\MediaAsset;
use App\Models\Project;
use App\Notifications\FinalPhotosReadyNotification;
use App\Notifications\RawPhotosUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Upload/download aset media project dan sinkron status workflow.
 */
class MediaAssetController extends Controller
{
    /** Upload aset media per project dengan versioning per tipe file. */
    public function store(Request $request, Project $project)
    {
        $rules = [
            'type' => ['required', 'in:' . implode(',', MediaAsset::TYPES)],
        ];
        $maxKb = 40960;
        if (in_array($request->type, MediaAsset::TYPES, true)) {
            $rules['files'] = ['required', 'array', 'max:50'];
            $rules['files.*'] = ['file', "max:{$maxKb}"];
        } else {
            $rules['file'] = ['required', 'file', "max:{$maxKb}"];
        }
        $request->validate($rules);

        $user = $request->user();
        $schedule = $project->schedule;

        $isCrewOnly = $user->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
            && ! $user->isRole(Role::ADMIN, Role::MANAGER, Role::CLIENT);
        $canUploadRaw = $user->isRole(Role::PHOTOGRAPHER)
            && $schedule
            && $schedule->photographer_id === $user->id;
        $canUploadFinal = $user->isRole(Role::EDITOR)
            && $schedule
            && $schedule->editor_id === $user->id;

        if ($isCrewOnly) {
            if ($request->type === MediaAsset::TYPE_RAW && ! $canUploadRaw) {
                abort(403, 'Anda tidak ditugaskan sebagai fotografer pada project ini.');
            }

            if ($request->type === MediaAsset::TYPE_FINAL && ! $canUploadFinal) {
                abort(403, 'Anda tidak ditugaskan sebagai editor pada project ini.');
            }
        }

        $files = in_array($request->type, MediaAsset::TYPES, true)
            ? $request->file('files')
            : [$request->file('file')];

        if ($request->type === MediaAsset::TYPE_FINAL && $project->mediaAssets()->where('type', MediaAsset::TYPE_FINAL)->exists()) {
            return back()->with('error', 'Foto final sudah diunggah, tidak dapat diunggah ulang.');
        }
        if ($request->type === MediaAsset::TYPE_RAW && $project->mediaAssets()->where('type', MediaAsset::TYPE_RAW)->exists()) {
            return back()->with('error', 'RAW sudah diunggah, tidak dapat diunggah ulang.');
        }

        $created = [];
        foreach ($files as $file) {
            $nextVersion = MediaAsset::where('project_id', $project->id)
                ->where('type', $request->type)
                ->max('version') + 1;

            $path = $file->storePublicly(
                "projects/{$project->id}/{$request->type}",
                'public'
            );

            $created[] = MediaAsset::create([
                'project_id' => $project->id,
                'type' => $request->type,
                'path' => $path,
                'uploaded_by' => $user->id,
                'version' => $nextVersion ?: 1,
                'expires_at' => now()->addDays(5),
            ]);
        }

        $this->updateProjectStatus($project, $request->type);
        $this->sendUploadNotification($project, $request->type);

        if ($request->wantsJson()) {
            return response()->json($created, 201);
        }

        return back()->with('success', 'Upload berhasil disimpan.');
    }

    /** Update status project berdasar tipe upload (raw->shoot_done, final->final). */
    protected function updateProjectStatus(Project $project, string $type): void
    {
        $statusMap = [
            MediaAsset::TYPE_RAW => Project::STATUS_SHOOT_DONE,
            MediaAsset::TYPE_FINAL => Project::STATUS_FINAL,
        ];

        if (isset($statusMap[$type])) {
            $project->update(['status' => $statusMap[$type]]);
        }
    }

    protected function sendUploadNotification(Project $project, string $type): void
    {
        $client = $project->booking->client;
        if (! $client) {
            return;
        }

        if ($type === MediaAsset::TYPE_RAW) {
            $client->notify(new RawPhotosUploadedNotification($project->id));
            return;
        }

        if ($type === MediaAsset::TYPE_FINAL) {
            $client->notify(new FinalPhotosReadyNotification($project->id));
        }
    }

    /** Unduh semua RAW milik project dalam bentuk zip (hanya untuk pemilik atau admin/manager). */
    public function downloadRaw(Project $project)
    {
        $user = request()->user();
        if ($user->role === Role::CLIENT && $project->booking->client_id !== $user->id) {
            abort(403);
        }

        $raws = $project->mediaAssets()->where('type', MediaAsset::TYPE_RAW)->orderBy('version')->get();
        if ($raws->isEmpty()) {
            return back()->with('error', 'Tidak ada file RAW untuk diunduh.');
        }

        $zip = new \ZipArchive();
        $tmpPath = storage_path('app/temp_raw_'.$project->id.'_'.time().'.zip');
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat arsip unduhan.');
        }

        foreach ($raws as $raw) {
            $fullPath = storage_path('app/public/'.$raw->path);
            if (! file_exists($fullPath)) {
                continue;
            }
            $ext = pathinfo($raw->path, PATHINFO_EXTENSION);
            $zip->addFile($fullPath, 'RAW/D'.$raw->version.'.'.$ext);
        }
        $zip->close();

        return response()->download($tmpPath, 'raw-booking-'.$project->booking_id.'.zip')->deleteFileAfterSend(true);
    }
}
