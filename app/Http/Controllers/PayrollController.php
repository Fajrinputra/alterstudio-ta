<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Booking;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Laporan operasional/pendapatan untuk manajer (tabel + grafik + export CSV).
 */
class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $categoryId = $request->input('category_id');
        $startAt = CarbonImmutable::parse($dateFrom)->startOfDay();
        $endAt = CarbonImmutable::parse($dateTo)->endOfDay();

        $bookings = Booking::with(['package','client'])
            ->whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn($q) => $q->whereHas('package', fn($p) => $p->where('category_id', $categoryId)))
            ->whereIn('status', ['DP_PAID','PAID'])
            ->get();
        $revenueTotal = $bookings->sum('total_price');

        $totalOrders = Booking::whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn($q) => $q->whereHas('package', fn($p) => $p->where('category_id', $categoryId)))
            ->count();
        $activeEditors = User::where('role', Role::EDITOR)->where('is_active', true)->count();
        $activePhotographers = User::where('role', Role::PHOTOGRAPHER)->where('is_active', true)->count();
        $activeClients = Booking::whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn($q) => $q->whereHas('package', fn($p) => $p->where('category_id', $categoryId)))
            ->whereIn('status', ['WAITING_PAYMENT','DP_PAID','PAID'])
            ->distinct('client_id')
            ->count('client_id');

        $photographerPerf = $this->performanceByRole(Role::PHOTOGRAPHER, $dateFrom, $dateTo, $categoryId);
        $editorPerf = $this->performanceByRole(Role::EDITOR, $dateFrom, $dateTo, $categoryId);

        // CSV export
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

        $packages = ServicePackage::orderBy('name')->get();
        $categories = ServiceCategory::orderBy('name')->get();

        return view('admin.payroll.index', compact(
            'dateFrom',
            'dateTo',
            'categoryId',
            'bookings',
            'revenueTotal',
            'totalOrders',
            'activeEditors',
            'activePhotographers',
            'activeClients',
            'photographerPerf',
            'editorPerf',
            'chart',
            'packages',
            'categories'
        ));
    }

    /** Ringkasan performa per role (project hitung dari schedule dalam rentang). */
    protected function performanceByRole(Role $role, string $start, string $end, ?int $categoryId = null)
    {
        $userField = $role === Role::PHOTOGRAPHER ? 'photographer' : 'editor';
        $column = $role === Role::PHOTOGRAPHER ? 'photographer_id' : 'editor_id';

        $schedules = Schedule::with(['project.booking.package', $userField])
            ->whereNotNull($column)
            ->whereBetween('start_at', [$start, $end.' 23:59:59'])
            ->when($categoryId, fn($q) => $q->whereHas('project.booking.package', fn($p) => $p->where('category_id', $categoryId)))
            ->get();

        return $schedules
            ->groupBy($column)
            ->map(function ($items) use ($userField) {
                $user = optional($items->first()->{$userField});
                $packages = $items->groupBy(fn($s)=>$s->project->booking->package->name ?? 'Tanpa Paket')
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

    protected function buildCsv(string $from, string $to, $bookings, $photographerPerf, $editorPerf, $revenueTotal): string
    {
        $lines = [];
        $lines[] = "Laporan Alter Studio;Periode;{$from};{$to}";
        $lines[] = "";
        $lines[] = "Pemesanan;Paket;Klien;Tanggal;Status;Total";
        foreach ($bookings as $b) {
            $lines[] = implode(';', [
                $b->id,
                $b->package->name ?? '-',
                $b->client->name ?? '-',
                $b->booking_date,
                $b->status,
                $b->total_price,
            ]);
        }
        $lines[] = ";;Total Pendapatan;;;" . $revenueTotal;
        $lines[] = "";
        $lines[] = "Kinerja Fotografer;Nama;Total Project";
        foreach ($photographerPerf as $p) {
            $lines[] = implode(';', ['',$p['name'],$p['total']]);
        }
        $lines[] = "Kinerja Editor;Nama;Total Project";
        foreach ($editorPerf as $e) {
            $lines[] = implode(';', ['',$e['name'],$e['total']]);
        }
        return implode("\n", $lines);
    }
}
