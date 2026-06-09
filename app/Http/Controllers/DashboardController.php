<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
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
            Role::ADMIN => $this->adminData(),
            Role::MANAGER => $this->managerData(),
            Role::OWNER => $this->ownerData(),
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

        $bookingMetrics = Booking::query()
            ->selectRaw('COUNT(*) as bookings')
            ->selectRaw('SUM(CASE WHEN status = ? AND confirmed_at IS NULL THEN 1 ELSE 0 END) as submitted', [Booking::STATUS_WAITING_PAYMENT])
            ->selectRaw('SUM(CASE WHEN status = ? AND confirmed_at IS NOT NULL THEN 1 ELSE 0 END) as waiting_payment', [Booking::STATUS_WAITING_PAYMENT])
            ->first();

        $metrics = [
            'bookings' => (int) ($bookingMetrics->bookings ?? 0),
            'submitted' => (int) ($bookingMetrics->submitted ?? 0),
            'waiting_payment' => (int) ($bookingMetrics->waiting_payment ?? 0),
            'projects_final' => Project::where('status', Project::STATUS_FINAL)->count(),
            'unscheduled' => Project::whereNull('start_at')
                ->whereHas('booking', fn ($booking) => $booking->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID]))
                ->count(),
        ];

        $schedules = Project::with(['booking', 'scheduleRecord.photographerAssignment.user', 'scheduleRecord.editorAssignment.user'])
            ->whereNotNull('start_at')
            ->where('start_at', '>=', Carbon::now()->subDay())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return compact('metrics', 'statusCounts', 'schedules');
    }

    protected function managerData(): array
    {
        // Dashboard manager fokus ke ringkasan bisnis dan komposisi role aktif.
        $statusCounts = Booking::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $bookingMetrics = Booking::query()
            ->selectRaw('COUNT(*) as bookings')
            ->selectRaw('SUM(CASE WHEN status = ? AND confirmed_at IS NULL THEN 1 ELSE 0 END) as submitted', [Booking::STATUS_WAITING_PAYMENT])
            ->selectRaw('SUM(CASE WHEN status = ? AND confirmed_at IS NOT NULL THEN 1 ELSE 0 END) as waiting_payment', [Booking::STATUS_WAITING_PAYMENT])
            ->first();

        $metrics = [
            'bookings' => (int) ($bookingMetrics->bookings ?? 0),
            'submitted' => (int) ($bookingMetrics->submitted ?? 0),
            'waiting_payment' => (int) ($bookingMetrics->waiting_payment ?? 0),
            'projects_final' => Project::where('status', Project::STATUS_FINAL)->count(),
            'unscheduled' => Project::whereNull('start_at')
                ->whereHas('booking', fn ($booking) => $booking->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID]))
                ->count(),
        ];

        $roleCounts = User::query()
            ->where('is_active', true)
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return compact('metrics', 'statusCounts', 'roleCounts');
    }

    protected function ownerData(): array
    {
        $data = $this->managerData();

        $data['metrics']['active_users'] = (int) collect($data['roleCounts'])->sum();
        $data['metrics']['managers'] = (int) ($data['roleCounts'][Role::MANAGER->value] ?? 0);
        $data['metrics']['admins'] = (int) ($data['roleCounts'][Role::ADMIN->value] ?? 0);
        $data['metrics']['revenue_received'] = (int) Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereHas('booking', fn ($booking) => $booking->where('status', '!=', Booking::STATUS_CANCELLED))
            ->sum('amount');

        return $data;
    }

    protected function photographerData(int $userId): array
    {
        // Antrian fotografer hanya project yang masih SCHEDULED.
        $upcoming = Project::with(['booking'])
            ->whereHas('scheduleRecord.users', fn ($q) => $q
                ->where('user_id', $userId)
                ->where('role', Role::PHOTOGRAPHER->value))
            ->where('status', Project::STATUS_SCHEDULED)
            ->whereHas('booking', fn ($booking) => $booking->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID]))
            ->whereNotNull('start_at')
            ->orderBy('start_at')
            ->get();

        $completed = Project::whereHas('scheduleRecord.users', fn ($q) => $q
                ->where('user_id', $userId)
                ->where('role', Role::PHOTOGRAPHER->value))
            ->where('status', Project::STATUS_FINAL)
            ->count();

        return [
            'upcoming' => $upcoming,
            'completed' => $completed,
        ];
    }

    protected function editorData(int $userId): array
    {
        // Antrian editor hanya project yang sudah memiliki permintaan edit.
        $queue = Project::with(['booking'])
            ->whereHas('scheduleRecord.users', fn ($q) => $q
                ->where('user_id', $userId)
                ->where('role', Role::EDITOR->value))
            ->where('status', Project::STATUS_EDITING)
            ->whereHas('booking', fn ($booking) => $booking->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID]))
            ->whereNotNull('edit_requested_at')
            ->whereNotNull('start_at')
            ->orderBy('start_at')
            ->get();

        $finalized = Project::whereHas('scheduleRecord.users', fn ($q) => $q
                ->where('user_id', $userId)
                ->where('role', Role::EDITOR->value))
            ->where('status', Project::STATUS_FINAL)
            ->count();

        return [
            'queue' => $queue,
            'finalized' => $finalized,
        ];
    }

}
