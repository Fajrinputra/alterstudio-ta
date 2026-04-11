<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioRoom;
use App\Models\User;
use App\Notifications\ScheduleAssignedNotification;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Pengaturan jadwal kru (fotografer/editor) per project.
 */
class ScheduleController extends Controller
{
    /** List project siap dijadwalkan untuk admin/manager. */
    public function index(Request $request)
    {
        $user = $request->user();
        $packageFilter = $request->query('package_id');
        $scheduleFilter = $request->query('schedule_status');
        $assignmentRoleFilter = $request->query('assignment_role');
        $crewUserFilter = $request->integer('crew_user_id') ?: null;

        $query = Project::with([
            'booking.client',
            'booking.package',
            'booking.studioLocation.rooms',
            'booking.studioRoom',
            'mediaAssets',
            'selections.mediaAsset',
            'photographer',
            'editor',
        ]);

        $isCrewOnly = $user->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
            && ! $user->isRole(Role::ADMIN, Role::MANAGER, Role::CLIENT);

        if ($isCrewOnly) {
            $query->where(function ($q) use ($user) {
                if ($user->isRole(Role::PHOTOGRAPHER)) {
                    $q->orWhere('photographer_id', $user->id);
                }
                if ($user->isRole(Role::EDITOR)) {
                    $q->orWhere('editor_id', $user->id);
                }
            })->whereNotNull('start_at');

            if ($assignmentRoleFilter === 'photographer') {
                $query->where('photographer_id', $user->id);
            }

            if ($assignmentRoleFilter === 'editor') {
                $query->where('editor_id', $user->id);
            }
            $readOnly = true;
        } else {
            $query
                ->when($assignmentRoleFilter === 'photographer', fn ($q) => $q->whereNotNull('photographer_id'))
                ->when($assignmentRoleFilter === 'editor', fn ($q) => $q->whereNotNull('editor_id'));
            $readOnly = false;
        }

        $projects = $query
            ->when($packageFilter, fn ($q) => $q->whereHas('booking', fn ($b) => $b->where('package_id', $packageFilter)))
            ->when($scheduleFilter === 'scheduled', fn ($q) => $q->whereNotNull('start_at'))
            ->when($scheduleFilter === 'unscheduled', fn ($q) => $q->whereNull('start_at'))
            ->when($crewUserFilter, function ($q) use ($crewUserFilter) {
                $q->where(function ($inner) use ($crewUserFilter) {
                    $inner->where('photographer_id', $crewUserFilter)
                        ->orWhere('editor_id', $crewUserFilter);
                });
            })
            ->orderByDesc('id')
            ->get();

        $unavailablePhotographers = [];
        $unavailableEditors = [];

        foreach ($projects as $project) {
            [$start, $end] = $this->buildScheduleWindow($project);
            $unavailableIds = $this->overlappingAssignedUserIds($start, $end, $project->id);
            $unavailablePhotographers[$project->id] = $unavailableIds;
            $unavailableEditors[$project->id] = $unavailableIds;
        }

        $photographers = User::withRole(Role::PHOTOGRAPHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $editors = User::withRole(Role::EDITOR)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $crewUsers = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->withRole(Role::PHOTOGRAPHER)
                    ->orWhere(function ($inner) {
                        $inner->withRole(Role::EDITOR);
                    });
            })
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
        $packages = ServicePackage::orderBy('name')->get();

        return view('admin.schedules.index', compact(
            'projects',
            'photographers',
            'editors',
            'crewUsers',
            'readOnly',
            'packages',
            'packageFilter',
            'scheduleFilter',
            'assignmentRoleFilter',
            'crewUserFilter',
            'unavailablePhotographers',
            'unavailableEditors'
        ));
    }

    /** Membuat / update jadwal project; cek booking sudah dibayar dan hindari overlap kru. */
    public function store(Request $request, Project $project)
    {
        $booking = $project->booking;
        if (! in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_DP_PAID], true)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Booking must be paid before scheduling'], 422)
                : back()->with('error', 'Booking harus dibayar (DP/Lunas) sebelum dijadwalkan.');
        }

        if ($project->hasSchedule()) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal sudah dikunci'], 422)
                : back()->with('error', 'Jadwal sudah dikunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'photographer_id' => ['required', 'exists:users,id'],
            'editor_id' => ['required', 'exists:users,id'],
            'studio_room_id' => ['required', 'exists:studio_rooms,id'],
        ]);

        if ($message = $this->validateAssignees($validated['photographer_id'], $validated['editor_id'])) {
            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $room = $this->resolveRoom($booking->studio_location_id, $validated['studio_room_id'], $request);
        if (! $room) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Ruangan tidak valid untuk cabang ini'], 422)
                : back()->with('error', 'Ruangan tidak valid untuk cabang ini.');
        }

        [$start, $end] = $this->buildScheduleWindow($project);
        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Schedule conflict detected'], 422)
                : back()->with('error', 'Bentrok jadwal terdeteksi untuk kru yang dipilih.');
        }

        $booking->update(['studio_room_id' => $room->id]);
        $project->update([
            'photographer_id' => $validated['photographer_id'],
            'editor_id' => $validated['editor_id'],
            'start_at' => $start,
            'end_at' => $end,
            'status' => Project::STATUS_SCHEDULED,
        ]);
        $project->load(['photographer', 'editor', 'booking']);

        $recipients = collect([$project->photographer, $project->editor])->filter();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ScheduleAssignedNotification($project->id));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $project->id,
                'status' => $project->status,
                'photographer_id' => $project->photographer_id,
                'editor_id' => $project->editor_id,
                'start_at' => optional($project->start_at)->toISOString(),
                'end_at' => optional($project->end_at)->toISOString(),
            ]);
        }

        return back()->with('success', 'Jadwal tersimpan dan dikunci.');
    }

    /** Edit jadwal jika project belum berjalan (aman diubah). */
    public function update(Request $request, Project $project)
    {
        if (! $project->hasSchedule()) {
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
            'studio_room_id' => ['required', 'exists:studio_rooms,id'],
        ]);

        if ($message = $this->validateAssignees($validated['photographer_id'], $validated['editor_id'])) {
            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $room = $this->resolveRoom($project->booking->studio_location_id, $validated['studio_room_id'], $request);
        if (! $room) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Ruangan tidak valid untuk cabang ini'], 422)
                : back()->with('error', 'Ruangan tidak valid untuk cabang ini.');
        }

        [$start, $end] = $this->buildScheduleWindow($project);
        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Schedule conflict detected'], 422)
                : back()->with('error', 'Bentrok jadwal terdeteksi.');
        }

        $project->booking->update(['studio_room_id' => $room->id]);
        $project->update([
            'photographer_id' => $validated['photographer_id'],
            'editor_id' => $validated['editor_id'],
            'start_at' => $start,
            'end_at' => $end,
        ]);
        $project->load(['photographer', 'editor']);

        $recipients = collect([$project->photographer, $project->editor])->filter();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ScheduleAssignedNotification($project->id));
        }

        return $request->wantsJson()
            ? response()->json([
                'id' => $project->id,
                'status' => $project->status,
                'photographer_id' => $project->photographer_id,
                'editor_id' => $project->editor_id,
                'start_at' => optional($project->start_at)->toISOString(),
                'end_at' => optional($project->end_at)->toISOString(),
            ])
            : back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    /** Hapus jadwal jika project belum berjalan (aman dihapus). */
    public function destroy(Request $request, Project $project)
    {
        if (! $project->hasSchedule()) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal belum tersedia'], 404)
                : back()->with('error', 'Jadwal belum tersedia.');
        }

        if (! $this->canModifySchedule($project)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal tidak bisa dihapus karena project sudah berjalan'], 422)
                : back()->with('error', 'Jadwal tidak bisa dihapus karena project sudah berjalan.');
        }

        $project->update([
            'photographer_id' => null,
            'editor_id' => null,
            'start_at' => null,
            'end_at' => null,
            'status' => Project::STATUS_DRAFT,
        ]);

        return $request->wantsJson()
            ? response()->json(['message' => 'Jadwal berhasil dihapus'])
            : back()->with('success', 'Jadwal berhasil dihapus.');
    }

    protected function hasOverlap(DateTimeInterface $start, DateTimeInterface $end, int $photographerId, int $editorId, int $projectId): bool
    {
        $assignedIds = array_values(array_unique([$photographerId, $editorId]));

        return Project::query()
            ->where(function ($q) use ($assignedIds) {
                $q->whereIn('photographer_id', $assignedIds)
                    ->orWhereIn('editor_id', $assignedIds);
            })
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('id', '!=', $projectId)
            ->whereNotNull('start_at')
            ->where('start_at', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $start->format('Y-m-d H:i:s'))
            ->exists();
    }

    protected function overlappingAssignedUserIds(DateTimeInterface $start, DateTimeInterface $end, int $projectId): array
    {
        $projects = Project::query()
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('id', '!=', $projectId)
            ->whereNotNull('start_at')
            ->where('start_at', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $start->format('Y-m-d H:i:s'))
            ->get(['photographer_id', 'editor_id']);

        return $projects
            ->flatMap(fn ($item) => [$item->photographer_id, $item->editor_id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function validateAssignees(int $photographerId, int $editorId): ?string
    {
        if ($photographerId === $editorId) {
            return 'Fotografer dan editor harus menggunakan akun yang berbeda.';
        }

        $photographer = User::withRole(Role::PHOTOGRAPHER)
            ->where('is_active', true)
            ->find($photographerId);
        if (! $photographer) {
            return 'Akun fotografer yang dipilih tidak memiliki akses fotografer aktif.';
        }

        $editor = User::withRole(Role::EDITOR)
            ->where('is_active', true)
            ->find($editorId);
        if (! $editor) {
            return 'Akun editor yang dipilih tidak memiliki akses editor aktif.';
        }

        return null;
    }

    protected function canModifySchedule(Project $project): bool
    {
        if (! in_array($project->status, [Project::STATUS_SCHEDULED, Project::STATUS_DRAFT], true)) {
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

    protected function resolveRoom(?int $locationId, int $roomId, Request $request): ?StudioRoom
    {
        return StudioRoom::where('id', $roomId)
            ->where('studio_location_id', $locationId)
            ->where('is_active', true)
            ->first();
    }
}
