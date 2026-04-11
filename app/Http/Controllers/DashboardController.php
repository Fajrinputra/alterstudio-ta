<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Menyusun data dashboard berdasarkan role user yang login.
 */
class DashboardController extends Controller
{
    /** Render dashboard dengan data ringkas sesuai role user. */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $role = $user->role instanceof Role ? $user->role : Role::from($user->role);
        $hasBothCrewRoles = $user->hasBothCrewRoles()
            && in_array($role, [Role::PHOTOGRAPHER, Role::EDITOR], true);

        $data = $hasBothCrewRoles ? $this->dualCrewData($user->id) : match ($role) {
            Role::CLIENT => $this->clientData($user->id),
            Role::ADMIN, Role::MANAGER => $this->adminData(),
            Role::PHOTOGRAPHER => $this->photographerData($user->id),
            Role::EDITOR => $this->editorData($user->id),
        };

        return view('dashboard', [
            'role' => $role,
            'hasBothCrewRoles' => $hasBothCrewRoles,
            'data' => $data,
        ]);
    }

    protected function clientData(int $userId): array
    {
        // Metrics client berbasis booking milik sendiri.
        $base = Booking::where('client_id', $userId);

        $metrics = [
            'bookings' => (clone $base)->count(),
            'waiting_payment' => (clone $base)->where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            // In progress: pembayaran sudah masuk, tetapi project belum final.
            'in_progress' => (clone $base)
                ->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID])
                ->where(function ($q) {
                    $q->whereDoesntHave('project')
                      ->orWhereHas('project', fn ($p) => $p->where('status', '!=', Project::STATUS_FINAL));
                })
                ->count(),
            'final_ready' => Project::whereHas('booking', fn ($q) => $q->where('client_id', $userId))
                ->where('status', Project::STATUS_FINAL)->count(),
        ];

        $latest = $base->with('project')
            ->latest()
            ->take(5)
            ->get();

        return compact('metrics', 'latest');
    }

    protected function adminData(): array
    {
        // Metrics global untuk admin/manager.
        $statusCounts = Booking::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $metrics = [
            'bookings' => Booking::count(),
            // Samakan dengan daftar pemesanan: menunggu pembayaran dihitung dari status booking.
            'waiting_payment' => Booking::where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            'projects_final' => Project::where('status', Project::STATUS_FINAL)->count(),
        ];

        $schedules = Project::with(['booking', 'photographer', 'editor'])
            ->whereNotNull('start_at')
            ->where('start_at', '>=', Carbon::now()->subDay())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return compact('metrics', 'statusCounts', 'schedules');
    }

    protected function photographerData(int $userId): array
    {
        // Antrian fotografer hanya project yang masih SCHEDULED.
        $upcoming = Project::with(['booking'])
            ->where('photographer_id', $userId)
            ->where('status', Project::STATUS_SCHEDULED)
            ->whereNotNull('start_at')
            ->orderBy('start_at')
            ->get();

        $completed = Project::where('photographer_id', $userId)
            ->where('status', Project::STATUS_FINAL)
            ->count();

        return [
            'upcoming' => $upcoming,
            'completed' => $completed,
        ];
    }

    protected function editorData(int $userId): array
    {
        // Antrian editor hanya project yang sudah dikunci client.
        $queue = Project::with(['booking'])
            ->where('editor_id', $userId)
            ->where('status', Project::STATUS_EDITING)
            ->where('selections_locked', true)
            ->whereNotNull('start_at')
            ->orderBy('start_at')
            ->get();

        $finalized = Project::where('editor_id', $userId)
            ->where('status', Project::STATUS_FINAL)
            ->count();

        return [
            'queue' => $queue,
            'finalized' => $finalized,
        ];
    }

    protected function dualCrewData(int $userId): array
    {
        $photographer = $this->photographerData($userId);
        $editor = $this->editorData($userId);

        return [
            'upcoming' => $photographer['upcoming'],
            'completed' => $photographer['completed'],
            'queue' => $editor['queue'],
            'finalized' => $editor['finalized'],
        ];
    }
}
