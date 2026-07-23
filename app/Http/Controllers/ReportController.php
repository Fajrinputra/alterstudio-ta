<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Modul laporan operasional untuk manajer dan owner.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->merge([
            'date_from' => $request->input('date_from', now()->subDays(30)->toDateString()),
            'date_to' => $request->input('date_to', now()->toDateString()),
        ]);

        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'download' => ['nullable', 'in:csv,xls,pdf'],
        ], [
            'date_from.required' => 'Tanggal awal laporan wajib diisi.',
            'date_from.date' => 'Tanggal awal laporan tidak valid.',
            'date_to.required' => 'Tanggal akhir laporan wajib diisi.',
            'date_to.date' => 'Tanggal akhir laporan tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir laporan harus sama dengan atau setelah tanggal awal.',
            'category_id.exists' => 'Kategori laporan yang dipilih tidak valid.',
            'download.in' => 'Format unduhan laporan tidak valid.',
        ]);

        $dateFrom = $validated['date_from'];
        $dateTo = $validated['date_to'];
        $isOwnerReport = $request->user()?->isRole(Role::OWNER) === true;
        $canExportReport = $request->user()?->isRole(Role::MANAGER) === true;
        $categoryId = $request->input('category_id') ? (int) $request->input('category_id') : null;
        $startAt = CarbonImmutable::parse($dateFrom)->startOfDay();
        $endAt = CarbonImmutable::parse($dateTo)->endOfDay();

        if ($request->has('download') && ! $canExportReport) {
            return redirect()
                ->route('reports.index', $request->only(['date_from', 'date_to']))
                ->with('error', 'Ekspor laporan dilakukan oleh manajer. Owner hanya melihat laporan berdasarkan periode.');
        }

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
        $paymentBreakdown = $this->paymentBreakdown($startAt, $endAt, $categoryId);
        $statusSummary = $this->bookingStatusSummary($startAt, $endAt, $categoryId);

        $photographerPerf = $this->performanceByRole(Role::PHOTOGRAPHER, $dateFrom, $dateTo, $categoryId);
        $editorPerf = $this->performanceByRole(Role::EDITOR, $dateFrom, $dateTo, $categoryId);
        $categories = ServiceCategory::orderBy('name')->get();
        $categoryLabel = $categoryId
            ? ($categories->firstWhere('id', (int) $categoryId)?->name ?? 'Kategori tidak ditemukan')
            : 'Semua Kategori';
        $exportedAt = now();
        $exportedBy = $request->user()?->name ?? '-';
        $reportTitle = 'Laporan Kinerja Kru Alterstudio';
        $fileBaseName = 'Laporan Kinerja Kru Alterstudio';

        if ($request->get('download') === 'csv') {
            try {
                $csv = $this->buildCsv(
                    $dateFrom,
                    $dateTo,
                    $categoryLabel,
                    $exportedAt,
                    $exportedBy,
                    $bookings,
                    $photographerPerf,
                    $editorPerf,
                    $revenueTotal,
                    $totalOrders,
                    $assignedPhotographers,
                    $assignedEditors,
                    $activeClients,
                    $isOwnerReport,
                    $paymentBreakdown,
                    $statusSummary,
                    $reportTitle
                );
            } catch (\Throwable $exception) {
                return $this->exportFailureResponse($request, 'CSV', $exception);
            }

            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, $fileBaseName.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->get('download') === 'xls') {
            $excelData = compact(
                'dateFrom',
                'dateTo',
                'categoryId',
                'categoryLabel',
                'exportedAt',
                'exportedBy',
                'reportTitle',
                'canExportReport',
                'bookings',
                'revenueTotal',
                'totalOrders',
                'assignedEditors',
                'assignedPhotographers',
                'activeClients',
                'isOwnerReport',
                'paymentBreakdown',
                'statusSummary',
                'photographerPerf',
                'editorPerf'
            );

            try {
                return response($this->renderExcelReport($excelData), 200, [
                    'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="'.$fileBaseName.'.xls"',
                ]);
            } catch (\Throwable $exception) {
                return $this->exportFailureResponse($request, 'Excel', $exception);
            }
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

        if ($request->get('download') === 'pdf') {
            $pdfData = compact(
                'dateFrom',
                'dateTo',
                'categoryId',
                'categoryLabel',
                'exportedAt',
                'exportedBy',
                'reportTitle',
                'canExportReport',
                'bookings',
                'revenueTotal',
                'totalOrders',
                'assignedEditors',
                'assignedPhotographers',
                'activeClients',
                'isOwnerReport',
                'paymentBreakdown',
                'statusSummary',
                'photographerPerf',
                'editorPerf'
            );

            try {
                return response($this->renderPdfReport($pdfData));
            } catch (\Throwable $exception) {
                return $this->exportFailureResponse($request, 'PDF', $exception);
            }
        }

        return view('admin.reports.index', compact(
            'dateFrom',
            'dateTo',
            'categoryId',
            'categoryLabel',
            'exportedAt',
            'exportedBy',
            'reportTitle',
            'canExportReport',
            'bookings',
            'revenueTotal',
            'totalOrders',
            'assignedEditors',
            'assignedPhotographers',
            'activeClients',
            'isOwnerReport',
            'paymentBreakdown',
            'statusSummary',
            'photographerPerf',
            'editorPerf',
            'chart',
            'categories'
        ));
    }

    /** @param array<string, mixed> $data */
    protected function renderPdfReport(array $data): string
    {
        return view('admin.reports.print', $data)->render();
    }

    /** @param array<string, mixed> $data */
    protected function renderExcelReport(array $data): string
    {
        return view('admin.reports.excel', $data)->render();
    }

    protected function exportFailureResponse(Request $request, string $format, \Throwable $exception)
    {
        Log::error('Report export failed.', [
            'format' => $format,
            'user_id' => $request->user()?->id,
            'error' => $exception->getMessage(),
        ]);

        return redirect()
            ->route('reports.index', $request->only(['date_from', 'date_to', 'category_id']))
            ->with('error', "Gagal membuat laporan {$format}. Silakan coba kembali.");
    }

    /**
     * Ringkasan performa per role berdasarkan jadwal yang aktif pada rentang laporan.
     */
    protected function performanceByRole(Role $role, string $start, string $end, ?int $categoryId = null)
    {
        $column = $role === Role::PHOTOGRAPHER ? 'photographer_id' : 'editor_id';

        $projects = Project::with(['booking.package', "scheduleRecord.{$this->scheduleRelationFor($role)}"])
            ->whereHas('scheduleRecord', fn ($q) => $q->whereNotNull($column))
            ->whereBetween('start_at', [$start, $end.' 23:59:59'])
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->get();

        return $projects
            ->groupBy(fn (Project $project) => $project->scheduleRecord?->{$column})
            ->map(function ($items) use ($role) {
                $relation = $this->scheduleRelationFor($role);
                $user = optional($items->first()->scheduleRecord?->{$relation});
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
            ->whereHas('scheduleRecord', fn ($q) => $q->whereNotNull($column))
            ->whereBetween('start_at', [$start, $end.' 23:59:59'])
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->with('scheduleRecord')
            ->get()
            ->pluck("scheduleRecord.{$column}")
            ->filter()
            ->unique()
            ->count();
    }

    protected function scheduleRelationFor(Role $role): string
    {
        return $role === Role::PHOTOGRAPHER ? 'photographer' : 'editor';
    }

    /**
     * Detail tambahan untuk owner: sumber pendapatan berdasarkan jenis pembayaran.
     */
    protected function paymentBreakdown($startAt, $endAt, ?int $categoryId = null)
    {
        return Payment::query()
            ->selectRaw('type, COUNT(*) as total, COALESCE(SUM(amount), 0) as amount')
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_at', [$startAt, $endAt])
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', Booking::STATUS_CANCELLED))
            ->when($categoryId, fn ($q) => $q->whereHas('booking.package', fn ($p) => $p->where('category_id', $categoryId)))
            ->groupBy('type')
            ->orderBy('type')
            ->get()
            ->map(fn ($item) => [
                'label' => $item->type === Payment::TYPE_DP ? 'DP' : 'Pelunasan / Lunas',
                'total' => (int) $item->total,
                'amount' => (int) $item->amount,
            ])
            ->values();
    }

    /**
     * Detail tambahan untuk owner: sebaran status pemesanan dalam periode.
     */
    protected function bookingStatusSummary($startAt, $endAt, ?int $categoryId = null)
    {
        return Booking::query()
            ->with('payments')
            ->whereBetween('booking_date', [$startAt, $endAt])
            ->when($categoryId, fn ($q) => $q->whereHas('package', fn ($p) => $p->where('category_id', $categoryId)))
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->statusLabel())
            ->map(fn ($items, string $label) => [
                'label' => $label,
                'total' => $items->count(),
                'amount' => (int) $items->sum(fn (Booking $booking) => $booking->paidAmount()),
            ])
            ->values();
    }

    protected function buildCsv(
        string $from,
        string $to,
        string $categoryLabel,
        $exportedAt,
        string $exportedBy,
        $bookings,
        $photographerPerf,
        $editorPerf,
        $revenueTotal,
        int $totalOrders,
        int $assignedPhotographers,
        int $assignedEditors,
        int $activeClients,
        bool $isOwnerReport,
        $paymentBreakdown,
        $statusSummary,
        string $reportTitle
    ): string {
        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "sep=;\r\n");
        $this->writeCsvRow($handle, [$reportTitle]);
        $this->writeCsvRow($handle, ['Alter Studio']);
        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Periode', $this->formatDate($from).' - '.$this->formatDate($to)]);
        $this->writeCsvRow($handle, ['Kategori', $categoryLabel]);
        $this->writeCsvRow($handle, ['Diekspor Oleh', $exportedBy]);
        $this->writeCsvRow($handle, ['Tanggal Ekspor', $exportedAt->format('d/m/Y H:i')]);
        $this->writeCsvRow($handle, []);

        $this->writeCsvRow($handle, ['Ringkasan Laporan']);
        $this->writeCsvRow($handle, ['Total Pemesanan', $totalOrders]);
        $this->writeCsvRow($handle, ['Pendapatan Diterima', $this->formatCurrency($revenueTotal)]);
        $this->writeCsvRow($handle, ['Fotografer Bertugas', $assignedPhotographers]);
        $this->writeCsvRow($handle, ['Editor Bertugas', $assignedEditors]);
        $this->writeCsvRow($handle, ['Klien Aktif', $activeClients]);
        $this->writeCsvRow($handle, []);

        if ($isOwnerReport) {
            $this->writeCsvRow($handle, ['Detail Owner - Ringkasan Pembayaran']);
            $this->writeCsvRow($handle, ['Jenis Pembayaran', 'Jumlah Transaksi', 'Nominal Diterima']);
            foreach ($paymentBreakdown as $item) {
                $this->writeCsvRow($handle, [$item['label'], $item['total'], $this->formatCurrency($item['amount'])]);
            }
            if ($paymentBreakdown->isEmpty()) {
                $this->writeCsvRow($handle, ['Belum ada pembayaran berhasil', '-', '-']);
            }

            $this->writeCsvRow($handle, []);
            $this->writeCsvRow($handle, ['Detail Owner - Status Pemesanan']);
            $this->writeCsvRow($handle, ['Status', 'Jumlah Pemesanan', 'Nominal Diterima']);
            foreach ($statusSummary as $item) {
                $this->writeCsvRow($handle, [$item['label'], $item['total'], $this->formatCurrency($item['amount'])]);
            }
            if ($statusSummary->isEmpty()) {
                $this->writeCsvRow($handle, ['Belum ada pemesanan pada periode ini', '-', '-']);
            }
            $this->writeCsvRow($handle, []);
        }

        $this->writeCsvRow($handle, ['Pemesanan dalam Periode']);
        $this->writeCsvRow($handle, ['No', 'ID Pemesanan', 'Paket', 'Klien', 'Tanggal', 'Status', 'Nilai Pemesanan']);

        foreach ($bookings as $index => $booking) {
            $this->writeCsvRow($handle, [
                $index + 1,
                '#'.$booking->id,
                $booking->package->name ?? '-',
                $booking->client->name ?? '-',
                optional($booking->booking_date)->format('d/m/Y'),
                $booking->statusLabel(),
                $this->formatCurrency((int) ($booking->total_price ?? 0)),
            ]);
        }

        if ($bookings->isEmpty()) {
            $this->writeCsvRow($handle, ['-', '-', 'Belum ada pemesanan pada periode ini', '-', '-', '-', '-']);
        }

        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Kinerja Fotografer']);
        $this->writeCsvRow($handle, ['No', 'Nama Fotografer', 'Total Project', 'Paket yang Ditangani']);

        foreach ($photographerPerf as $index => $photographer) {
            $this->writeCsvRow($handle, [
                $index + 1,
                $photographer['name'],
                $photographer['total'],
                $this->packageBreakdown($photographer['packages']),
            ]);
        }

        if ($photographerPerf->isEmpty()) {
            $this->writeCsvRow($handle, ['-', 'Belum ada data fotografer pada periode ini', '-', '-']);
        }

        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Kinerja Editor']);
        $this->writeCsvRow($handle, ['No', 'Nama Editor', 'Total Project', 'Paket yang Ditangani']);

        foreach ($editorPerf as $index => $editor) {
            $this->writeCsvRow($handle, [
                $index + 1,
                $editor['name'],
                $editor['total'],
                $this->packageBreakdown($editor['packages']),
            ]);
        }

        if ($editorPerf->isEmpty()) {
            $this->writeCsvRow($handle, ['-', 'Belum ada data editor pada periode ini', '-', '-']);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF".$csv;
    }

    protected function writeCsvRow($handle, array $row): void
    {
        fputcsv($handle, $row, ';');
    }

    protected function packageBreakdown($packages): string
    {
        return collect($packages)
            ->map(fn ($count, $name) => $name.' ('.$count.')')
            ->values()
            ->implode(', ');
    }

    protected function formatDate(?string $date): string
    {
        return $date ? CarbonImmutable::parse($date)->format('d/m/Y') : '-';
    }

    protected function formatCurrency(int|float $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
