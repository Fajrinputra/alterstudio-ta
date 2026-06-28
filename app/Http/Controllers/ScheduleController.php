<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\ProjectScheduleUser;
use App\Models\ServicePackage;
use App\Models\StudioRoom;
use App\Models\User;
use App\Notifications\ScheduleAssignedNotification;
use App\Support\DeferredNotification;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'scheduleRecord.photographerAssignment.user',
            'scheduleRecord.editorAssignment.user',
        ])->whereHas('booking');

        $isCrewOnly = $user->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
            && ! $user->isRole(Role::OWNER, Role::ADMIN, Role::MANAGER, Role::CLIENT);

        if ($isCrewOnly) {
            $query->whereHas('scheduleRecord.users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereNotNull('start_at');

            if ($assignmentRoleFilter === 'photographer') {
                $query->whereHas('scheduleRecord.users', fn ($q) => $q
                    ->where('user_id', $user->id)
                    ->where('role', Role::PHOTOGRAPHER->value));
            }

            if ($assignmentRoleFilter === 'editor') {
                $query->whereHas('scheduleRecord.users', fn ($q) => $q
                    ->where('user_id', $user->id)
                    ->where('role', Role::EDITOR->value));
            }
            $readOnly = true;
        } else {
            $query
                ->when($assignmentRoleFilter === 'photographer', fn ($q) => $q
                    ->whereHas('scheduleRecord.users', fn ($users) => $users->where('role', Role::PHOTOGRAPHER->value)))
                ->when($assignmentRoleFilter === 'editor', fn ($q) => $q
                    ->whereHas('scheduleRecord.users', fn ($users) => $users->where('role', Role::EDITOR->value)));
            $readOnly = false;
        }

        $projects = $query
            ->when($packageFilter, fn ($q) => $q->whereHas('booking', fn ($b) => $b->where('package_id', $packageFilter)))
            ->when($scheduleFilter === 'scheduled', fn ($q) => $q->whereNotNull('start_at'))
            ->when($scheduleFilter === 'unscheduled', fn ($q) => $q->whereNull('start_at'))
            ->when($crewUserFilter, function ($q) use ($crewUserFilter) {
                $q->whereHas('scheduleRecord.users', fn ($users) => $users->where('user_id', $crewUserFilter));
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $unavailablePhotographers = [];
        $unavailableEditors = [];
        $projectWindows = [];

        foreach ($projects as $project) {
            $projectWindows[$project->id] = $this->buildScheduleWindow($project);
        }

        $existingSchedules = collect();
        if ($projectWindows !== []) {
            $rangeStart = collect($projectWindows)->pluck(0)->sortBy(fn ($date) => $date->getTimestamp())->first();
            $rangeEnd = collect($projectWindows)->pluck(1)->sortByDesc(fn ($date) => $date->getTimestamp())->first();

            $existingSchedules = ProjectSchedule::query()
                ->with('users:id,project_schedule_id,user_id')
                ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
                ->where('start_at', '<', $rangeEnd->format('Y-m-d H:i:s'))
                ->where('end_at', '>', $rangeStart->format('Y-m-d H:i:s'))
                ->get();
        }

        foreach ($projects as $project) {
            [$start, $end] = $projectWindows[$project->id];
            $unavailableIds = $existingSchedules
                ->filter(fn (ProjectSchedule $schedule) => $schedule->project_id !== $project->id
                    && Carbon::parse($schedule->start_at)->lt($end)
                    && Carbon::parse($schedule->end_at)->gt($start))
                ->flatMap(fn (ProjectSchedule $schedule) => $schedule->users->pluck('user_id'))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

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
        if (! $booking) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Data pemesanan untuk project ini tidak ditemukan.'], 404)
                : back()->with('error', 'Data pemesanan untuk project ini tidak ditemukan.');
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Pemesanan sudah dibatalkan dan tidak dapat dijadwalkan.'], 422)
                : back()->with('error', 'Pemesanan sudah dibatalkan dan tidak dapat dijadwalkan.');
        }

        if (! in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_DP_PAID], true)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Pemesanan harus sudah dibayar minimal DP sebelum dijadwalkan.'], 422)
                : back()->with('error', 'Pemesanan harus sudah dibayar minimal DP sebelum dijadwalkan.');
        }

        if ($project->hasSchedule()) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal sudah tersimpan dan tidak dapat diubah dari proses ini.'], 422)
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
        if ($this->hasRoomOverlap($booking, $room->id, $start, $end)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.'], 422)
                : back()->with('error', 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.');
        }

        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.'], 422)
                : back()->with('error', 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.');
        }

        DB::transaction(function () use ($booking, $room, $project, $validated, $start, $end, $request) {
            $booking->update(['studio_room_id' => $room->id]);
            $project->update([
                'start_at' => $start,
                'end_at' => $end,
                'status' => Project::STATUS_SCHEDULED,
            ]);
            $this->syncScheduleRecord($project, $booking, $room, $validated, $start, $end, $request->user()?->id);
        });

        $project->load(['booking', 'scheduleRecord.photographerAssignment.user', 'scheduleRecord.editorAssignment.user']);

        $recipients = collect([$project->photographer, $project->editor])->filter();
        if ($recipients->isNotEmpty()) {
            DeferredNotification::send($recipients, new ScheduleAssignedNotification($project->id));
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
        if (! $project->booking) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Data pemesanan untuk project ini tidak ditemukan.'], 404)
                : back()->with('error', 'Data pemesanan untuk project ini tidak ditemukan.');
        }

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
        if ($this->hasRoomOverlap($project->booking, $room->id, $start, $end)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.'], 422)
                : back()->with('error', 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.');
        }

        if ($this->hasOverlap($start, $end, $validated['photographer_id'], $validated['editor_id'], $project->id)) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.'], 422)
                : back()->with('error', 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.');
        }

        DB::transaction(function () use ($project, $room, $validated, $start, $end, $request) {
            $project->booking->update(['studio_room_id' => $room->id]);
            $project->update([
                'start_at' => $start,
                'end_at' => $end,
            ]);
            $this->syncScheduleRecord($project, $project->booking, $room, $validated, $start, $end, $request->user()?->id);
        });

        $project->load(['scheduleRecord.photographerAssignment.user', 'scheduleRecord.editorAssignment.user']);

        $recipients = collect([$project->photographer, $project->editor])->filter();
        if ($recipients->isNotEmpty()) {
            DeferredNotification::send($recipients, new ScheduleAssignedNotification($project->id));
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

        DB::transaction(function () use ($project) {
            $project->scheduleRecord?->delete();
            $project->update([
                'start_at' => null,
                'end_at' => null,
                'status' => Project::STATUS_DRAFT,
            ]);
        });

        return $request->wantsJson()
            ? response()->json(['message' => 'Jadwal berhasil dihapus'])
            : back()->with('success', 'Jadwal berhasil dihapus.');
    }

    protected function hasOverlap(DateTimeInterface $start, DateTimeInterface $end, int $photographerId, int $editorId, int $projectId): bool
    {
        $assignedIds = array_values(array_unique([$photographerId, $editorId]));

        $scheduleOverlap = ProjectSchedule::query()
            ->whereHas('users', fn ($q) => $q->whereIn('user_id', $assignedIds))
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('project_id', '!=', $projectId)
            ->where('start_at', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $start->format('Y-m-d H:i:s'))
            ->exists();

        return $scheduleOverlap;
    }

    protected function overlappingAssignedUserIds(DateTimeInterface $start, DateTimeInterface $end, int $projectId): array
    {
        $scheduleUserIds = ProjectSchedule::query()
            ->with('users:id,project_schedule_id,user_id')
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('project_id', '!=', $projectId)
            ->where('start_at', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $start->format('Y-m-d H:i:s'))
            ->get()
            ->flatMap(fn (ProjectSchedule $schedule) => $schedule->users->pluck('user_id'))
            ->map(fn ($id) => (int) $id);

        return $scheduleUserIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function hasRoomOverlap(Booking $booking, int $roomId, DateTimeInterface $start, DateTimeInterface $end): bool
    {
        $buffer = max(0, (int) config('studio.booking_buffer_minutes', 15));
        $candidateStart = Carbon::parse($start->format('Y-m-d H:i:s'));
        $candidateBlockedEnd = Carbon::parse($end->format('Y-m-d H:i:s'))->addMinutes($buffer);

        $scheduleOverlap = ProjectSchedule::query()
            ->where('booking_id', '!=', $booking->id)
            ->where('studio_room_id', $roomId)
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('start_at', '<', $candidateBlockedEnd->format('Y-m-d H:i:s'))
            ->where('end_at', '>', $candidateStart->format('Y-m-d H:i:s'))
            ->exists();

        if ($scheduleOverlap) {
            return true;
        }

        return Booking::query()
            ->with('package:id,duration_minutes')
            ->whereKeyNot($booking->id)
            ->where('studio_room_id', $roomId)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->whereDate('booking_date', $candidateStart->toDateString())
            ->get()
            ->contains(function (Booking $otherBooking) use ($candidateStart, $candidateBlockedEnd, $buffer) {
                $duration = $otherBooking->effectiveDurationMinutes();
                $otherStart = Carbon::parse(
                    Carbon::parse($otherBooking->booking_date)->toDateString().' '.($otherBooking->booking_time ?? '00:00')
                );
                $otherBlockedEnd = $otherStart->copy()->addMinutes($duration + $buffer);

                return $candidateStart->lt($otherBlockedEnd) && $candidateBlockedEnd->gt($otherStart);
            });
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
        if (! $project->bookingAllowsScheduling()) {
            return false;
        }

        if (! in_array($project->status, [Project::STATUS_SCHEDULED, Project::STATUS_DRAFT], true)) {
            return false;
        }

        if ($project->selections_locked) {
            return false;
        }

        if ($project->hasPostProductionActivity()) {
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
        if (! $booking) {
            $start = now();

            return [$start, $start->clone()];
        }

        $dateString = $booking->booking_date ? Carbon::parse($booking->booking_date)->toDateString() : now()->toDateString();
        $timeString = $booking->booking_time ?? '00:00';
        $start = Carbon::parse($dateString.' '.$timeString);
        $duration = $booking->effectiveDurationMinutes();
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

    /**
     * @param  array{photographer_id:int, editor_id:int, studio_room_id:int}  $validated
     */
    protected function syncScheduleRecord(Project $project, Booking $booking, StudioRoom $room, array $validated, DateTimeInterface $start, DateTimeInterface $end, ?int $scheduledBy): ProjectSchedule
    {
        $schedule = ProjectSchedule::updateOrCreate(
            ['project_id' => $project->id],
            [
                'booking_id' => $booking->id,
                'studio_location_id' => (int) $booking->studio_location_id,
                'studio_room_id' => $room->id,
                'scheduled_by' => $scheduledBy,
                'start_at' => $start,
                'end_at' => $end,
                'status' => ProjectSchedule::STATUS_SCHEDULED,
            ]
        );

        $schedule->users()->delete();
        ProjectScheduleUser::create([
            'project_schedule_id' => $schedule->id,
            'user_id' => $validated['photographer_id'],
            'role' => Role::PHOTOGRAPHER->value,
        ]);
        ProjectScheduleUser::create([
            'project_schedule_id' => $schedule->id,
            'user_id' => $validated['editor_id'],
            'role' => Role::EDITOR->value,
        ]);

        return $schedule;
    }
}
