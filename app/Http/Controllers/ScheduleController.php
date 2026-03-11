<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Schedule;
use App\Models\ServicePackage;
use App\Notifications\ScheduleAssignedNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Enums\Role;
use Illuminate\Support\Facades\Notification;

/**
 * Pengaturan jadwal kru (fotografer/editor) per project.
 */
class ScheduleController extends Controller
{
    /** List project siap dijadwalkan untuk admin/manager. */
    public function index(Request $request)
    {
        $user = request()->user();

        // Admin/Manager lihat semua; Photographer/Editor hanya yang ditugaskan
        $withRelations = ['booking.client', 'booking.package', 'schedule', 'mediaAssets', 'selections.mediaAsset'];

        $packageFilter = $request->query('package_id');

        if (in_array($user->role, [Role::PHOTOGRAPHER, Role::EDITOR])) {
            $projects = Project::with($withRelations)
                ->whereHas('schedule', function ($q) use ($user) {
                    $q->when($user->role === Role::PHOTOGRAPHER, fn($qq)=>$qq->where('photographer_id', $user->id))
                      ->when($user->role === Role::EDITOR, fn($qq)=>$qq->where('editor_id', $user->id));
                })
                ->when($packageFilter, fn($q)=>$q->whereHas('booking', fn($b)=>$b->where('package_id', $packageFilter)))
                ->orderByDesc('id')
                ->get();
            $readOnly = true;
        } else {
            $projects = Project::with($withRelations)
                ->when($packageFilter, fn($q)=>$q->whereHas('booking', fn($b)=>$b->where('package_id', $packageFilter)))
                ->orderByDesc('id')
                ->get();
            $readOnly = false;
        }

        $photographers = \App\Models\User::where('role', \App\Enums\Role::PHOTOGRAPHER)->orderBy('name')->get();
        $editors = \App\Models\User::where('role', \App\Enums\Role::EDITOR)->orderBy('name')->get();
        $packages = ServicePackage::orderBy('name')->get();

        return view('admin.schedules.index', compact('projects', 'photographers', 'editors', 'readOnly', 'packages', 'packageFilter'));
    }

    /** Membuat / update jadwal project; cek booking sudah dibayar dan hindari overlap kru. */
    public function store(Request $request, Project $project)
    {
        $booking = $project->booking;
        if ($booking->status !== 'PAID' && $booking->status !== 'DP_PAID') {
            return response()->json(['message' => 'Booking must be paid before scheduling'], 422);
        }

        // Kunci: jika sudah ada jadwal, tidak boleh diubah lagi
        if ($project->schedule) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal sudah dikunci'], 422)
                : back()->with('error', 'Jadwal sudah dikunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'photographer_id' => ['required', 'exists:users,id'],
            'editor_id' => ['required', 'exists:users,id'],
        ]);

        [$start, $end] = $this->buildScheduleWindow($project);
        $location = $booking->location;

        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return response()->json(['message' => 'Schedule conflict detected'], 422);
        }

        $schedule = Schedule::updateOrCreate(
            ['project_id' => $project->id],
            [
                'photographer_id' => $validated['photographer_id'],
                'editor_id' => $validated['editor_id'],
                'start_at' => $start,
                'end_at' => $end,
                'location' => $location,
                'status' => 'SCHEDULED',
            ]
        );

        $project->update(['status' => 'SCHEDULED']);

        $recipients = collect([$schedule->photographer, $schedule->editor])->filter();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ScheduleAssignedNotification($schedule->id));
        }

        if ($request->wantsJson()) {
            return response()->json($schedule->load(['photographer', 'editor']));
        }
        return back()->with('success', 'Jadwal tersimpan dan dikunci.');
    }

    /** Edit jadwal jika project belum berjalan (aman diubah). */
    public function update(Request $request, Project $project)
    {
        if (! $project->schedule) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal belum tersedia'], 404)
                : back()->with('error', 'Jadwal belum tersedia.');
        }

        if (! $this->canModifySchedule($project)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal tidak bisa diubah karena project sudah berjalan'], 422)
                : back()->with('error', 'Jadwal tidak bisa diubah karena project sudah berjalan.');
        }

        $validated = $request->validate([
            'photographer_id' => ['required', 'exists:users,id'],
            'editor_id' => ['required', 'exists:users,id'],
        ]);

        [$start, $end] = $this->buildScheduleWindow($project);

        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Schedule conflict detected'], 422)
                : back()->with('error', 'Bentrok jadwal terdeteksi.');
        }

        $project->schedule->update([
            'photographer_id' => $validated['photographer_id'],
            'editor_id' => $validated['editor_id'],
            'start_at' => $start,
            'end_at' => $end,
            'location' => $project->booking->location,
        ]);

        $project->schedule->refresh()->load(['photographer', 'editor']);
        $recipients = collect([$project->schedule->photographer, $project->schedule->editor])->filter();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ScheduleAssignedNotification($project->schedule->id));
        }

        return $request->wantsJson()
            ? response()->json($project->schedule->load(['photographer', 'editor']))
            : back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    /** Hapus jadwal jika project belum berjalan (aman dihapus). */
    public function destroy(Request $request, Project $project)
    {
        if (! $project->schedule) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal belum tersedia'], 404)
                : back()->with('error', 'Jadwal belum tersedia.');
        }

        if (! $this->canModifySchedule($project)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal tidak bisa dihapus karena project sudah berjalan'], 422)
                : back()->with('error', 'Jadwal tidak bisa dihapus karena project sudah berjalan.');
        }

        $project->schedule()->delete();
        $project->update(['status' => 'DRAFT']);

        return $request->wantsJson()
            ? response()->json(['message' => 'Jadwal berhasil dihapus'])
            : back()->with('success', 'Jadwal berhasil dihapus.');
    }

    /** Cek bentrok jadwal untuk fotografer/editor di rentang waktu yang sama. */
    protected function hasOverlap(\DateTimeInterface $start, \DateTimeInterface $end, int $photographerId, int $editorId, int $projectId): bool
    {
        return Schedule::where(function ($q) use ($photographerId, $editorId) {
                $q->where('photographer_id', $photographerId)
                    ->orWhere('editor_id', $editorId);
            })
            ->where('project_id', '!=', $projectId)
            ->where('start_at', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $start->format('Y-m-d H:i:s'))
            ->exists();
    }

    protected function canModifySchedule(Project $project): bool
    {
        if (! in_array($project->status, ['SCHEDULED', 'DRAFT'], true)) {
            return false;
        }

        if ($project->selections_locked) {
            return false;
        }

        if ($project->mediaAssets()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    protected function buildScheduleWindow(Project $project): array
    {
        $booking = $project->booking;
        $dateString = $booking->booking_date ? Carbon::parse($booking->booking_date)->toDateString() : now()->toDateString();
        $timeString = $booking->booking_time ?? '00:00';
        $start = Carbon::parse($dateString.' '.$timeString);
        $duration = $booking->package->duration_minutes ?? 60;
        $end = $start->clone()->addMinutes($duration);

        return [$start, $end];
    }
}
