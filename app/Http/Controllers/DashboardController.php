<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\Schedule;
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

        $data = match ($role) {
            Role::CLIENT => $this->clientData($user->id),
            Role::ADMIN, Role::MANAGER => $this->adminData(),
            Role::PHOTOGRAPHER => $this->photographerData($user->id),
            Role::EDITOR => $this->editorData($user->id),
        };

        return view('dashboard', [
            'role' => $role,
            'data' => $data,
        ]);
    }

    protected function clientData(int $userId): array
    {
        // Metrics client berbasis booking milik sendiri.
        $base = Booking::where('client_id', $userId);

        $metrics = [
            'bookings' => (clone $base)->count(),
            'waiting_payment' => (clone $base)->where('status', 'WAITING_PAYMENT')->count(),
            // In progress: pembayaran sudah masuk, tetapi project belum final.
            'in_progress' => (clone $base)
                ->whereIn('status', ['DP_PAID', 'PAID'])
                ->where(function ($q) {
                    $q->whereDoesntHave('project')
                      ->orWhereHas('project', fn ($p) => $p->where('status', '!=', 'FINAL'));
                })
                ->count(),
            'final_ready' => Project::whereHas('booking', fn ($q) => $q->where('client_id', $userId))
                ->where('status', 'FINAL')->count(),
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
            'waiting_payment' => Booking::where('status', 'WAITING_PAYMENT')->count(),
            'projects_final' => Project::where('status', 'FINAL')->count(),
        ];

        $schedules = Schedule::with(['project.booking', 'photographer', 'editor'])
            ->where('start_at', '>=', Carbon::now()->subDay())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return compact('metrics', 'statusCounts', 'schedules');
    }

    protected function photographerData(int $userId): array
    {
        // Antrian fotografer hanya project yang masih SCHEDULED.
        $upcoming = Schedule::with(['project.booking'])
            ->where('photographer_id', $userId)
            ->whereHas('project', fn ($q) => $q->where('status', 'SCHEDULED'))
            ->orderBy('start_at')
            ->get();

        $completed = Project::whereHas('schedule', fn ($q) => $q->where('photographer_id', $userId))
            ->where('status', 'FINAL')
            ->count();

        return [
            'upcoming' => $upcoming,
            'completed' => $completed,
        ];
    }

    protected function editorData(int $userId): array
    {
        // Antrian editor hanya project yang sudah dikunci client.
        $queue = Schedule::with(['project.booking'])
            ->where('editor_id', $userId)
            ->whereHas('project', fn ($q) => $q->where('status', 'EDITING')->where('selections_locked', true))
            ->orderBy('start_at')
            ->get();

        $finalized = Project::whereHas('schedule', fn ($q) => $q->where('editor_id', $userId))
            ->where('status', 'FINAL')
            ->count();

        return [
            'queue' => $queue,
            'finalized' => $finalized,
        ];
    }
}
