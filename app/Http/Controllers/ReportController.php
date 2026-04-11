<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Modul laporan operasional untuk manager.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $categoryId = $request->input('category_id');
        $startAt = CarbonImmutable::parse($dateFrom)->startOfDay();
        $endAt = CarbonImmutable::parse($dateTo)->endOfDay();

        $bookings = Booking::with(['package', 'client'])
            ->whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn ($q) => $q->whereHas('package', fn ($p) => $p->where('category_id', $categoryId)))
            ->whereIn('status', [Booking::STATUS_DP_PAID, Booking::STATUS_PAID])
            ->get();

        $revenueTotal = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->sum('amount');

        $totalOrders = Booking::whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn ($q) => $q->whereHas('package', fn ($p) => $p->where('category_id', $categoryId)))
            ->count();

        $assignedEditors = $this->scheduledAssigneesCount(Role::EDITOR, $dateFrom, $dateTo, $categoryId);
        $assignedPhotographers = $this->scheduledAssigneesCount(Role::PHOTOGRAPHER, $dateFrom, $dateTo, $categoryId);
        $activeClients = Booking::whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn ($q) => $q->whereHas('package', fn ($p) => $p->where('category_id', $categoryId)))
            ->whereIn('status', [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_DP_PAID, Booking::STATUS_PAID])
            ->distinct('client_id')
            ->count('client_id');

        $photographerPerf = $this->performanceByRole(Role::PHOTOGRAPHER, $dateFrom, $dateTo, $categoryId);
        $editorPerf = $this->performanceByRole(Role::EDITOR, $dateFrom, $dateTo, $categoryId);

        if ($request->get('download') === 'csv') {
            $csv = $this->buildCsv($dateFrom, $dateTo, $bookings, $photographerPerf, $editorPerf, $revenueTotal);
            $filename = "laporan-{$dateFrom}-{$dateTo}.csv";

            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $chart = [
            'photographers' => [
                'labels' => $photographerPerf->pluck('name'),
                'data' => $photographerPerf->pluck('total'),
            ],
            'editors' => [
                'labels' => $editorPerf->pluck('name'),
                'data' => $editorPerf->pluck('total'),
            ],
        ];

        $categories = ServiceCategory::orderBy('name')->get();

        return view('admin.reports.index', compact(
            'dateFrom',
            'dateTo',
            'categoryId',
            'bookings',
            'revenueTotal',
            'totalOrders',
            'assignedEditors',
            'assignedPhotographers',
            'activeClients',
            'photographerPerf',
            'editorPerf',
            'chart',
            'categories'
        ));
    }

    /**
     * Ringkasan performa per role berdasarkan jadwal yang aktif pada rentang laporan.
     */
    protected function performanceByRole(Role $role, string $start, string $end, ?int $categoryId = null)
    {
        $userField = $role === Role::PHOTOGRAPHER ? 'photographer' : 'editor';
        $column = $role === Role::PHOTOGRAPHER ? 'photographer_id' : 'editor_id';

        $projects = Project::with(['booking.package', $userField])
            ->whereNotNull($column)
            ->whereBetween('start_at', [$start, $end . ' 23:59:59'])
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->get();

        return $projects
            ->groupBy($column)
            ->map(function ($items) use ($userField) {
                $user = optional($items->first()->{$userField});
                $packages = $items->groupBy(fn ($project) => $project->booking->package->name ?? 'Tanpa Paket')
                    ->map->count();

                return [
                    'id' => $user?->id,
                    'name' => $user?->name ?? 'Tidak diketahui',
                    'total' => $items->count(),
                    'packages' => $packages,
                ];
            })
            ->values();
    }

    /**
     * Jumlah kru unik yang benar-benar mendapat jadwal dalam periode laporan.
     */
    protected function scheduledAssigneesCount(Role $role, string $start, string $end, ?int $categoryId = null): int
    {
        $column = $role === Role::PHOTOGRAPHER ? 'photographer_id' : 'editor_id';

        return Project::query()
            ->whereNotNull($column)
            ->whereBetween('start_at', [$start, $end . ' 23:59:59'])
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->distinct($column)
            ->count($column);
    }

    protected function buildCsv(string $from, string $to, $bookings, $photographerPerf, $editorPerf, $revenueTotal): string
    {
        $lines = [];
        $lines[] = "Laporan Alter Studio;Periode;{$from};{$to}";
        $lines[] = "";
        $lines[] = "Pemesanan;Paket;Klien;Tanggal;Status;Nilai Pemesanan";

        foreach ($bookings as $booking) {
            $lines[] = implode(';', [
                $booking->id,
                $booking->package->name ?? '-',
                $booking->client->name ?? '-',
                $booking->booking_date,
                $booking->status,
                $booking->total_price,
            ]);
        }

        $lines[] = ";;Pendapatan Diterima;;;" . $revenueTotal;
        $lines[] = "";
        $lines[] = "Kinerja Fotografer;Nama;Total Project";

        foreach ($photographerPerf as $photographer) {
            $lines[] = implode(';', ['', $photographer['name'], $photographer['total']]);
        }

        $lines[] = "Kinerja Editor;Nama;Total Project";

        foreach ($editorPerf as $editor) {
            $lines[] = implode(';', ['', $editor['name'], $editor['total']]);
        }

        return implode("\n", $lines);
    }
}
