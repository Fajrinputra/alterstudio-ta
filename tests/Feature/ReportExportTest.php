<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\ReportController;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_rejects_invalid_date_range_and_download_format(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->actingAs($manager)
            ->get(route('reports.index', [
                'date_from' => '2026-06-20',
                'date_to' => '2026-06-01',
                'download' => 'xlsx',
            ]))
            ->assertSessionHasErrors(['date_to', 'download']);
    }

    /**
     * Pengujian: Manajer melihat laporan dengan filter periode dan kategori.
     * Hasil yang diharapkan: ringkasan laporan menampilkan data pendapatan, pesanan, dan kru sesuai filter.
     */
    public function test_manager_can_view_report_with_filtered_summary_data(): void
    {
        [$manager, $category] = $this->seedReportScenario();

        $response = $this->actingAs($manager)->get(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
        ]));

        $response->assertOk()
            ->assertViewHas('reportTitle', 'Laporan Kinerja Kru Alterstudio')
            ->assertViewHas('categoryLabel', $category->name)
            ->assertViewHas('revenueTotal', 500000)
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('assignedPhotographers', 1)
            ->assertViewHas('assignedEditors', 1)
            ->assertSee('Total Pendapatan', false);
    }

    /**
     * Pengujian: ekspor laporan CSV oleh Manajer.
     * Hasil yang diharapkan: file CSV berisi kop laporan, metadata periode, dan tabel ringkasan.
     */
    public function test_csv_export_contains_letterhead_metadata_and_report_tables(): void
    {
        [$manager, $category] = $this->seedReportScenario();

        $response = $this->actingAs($manager)->get(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
            'download' => 'csv',
        ]));

        $response->assertOk();
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Laporan Kinerja Kru Alterstudio.csv', urldecode((string) $disposition));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('sep=;', $csv);
        $this->assertStringContainsString('Laporan Kinerja Kru Alterstudio', $csv);
        $this->assertStringContainsString('Alter Studio', $csv);
        $this->assertStringContainsString('Periode', $csv);
        $this->assertStringContainsString($manager->name, $csv);
        $this->assertStringContainsString('Ringkasan Laporan', $csv);
        $this->assertStringContainsString('Pemesanan dalam Periode', $csv);
        $this->assertStringContainsString('Kinerja Fotografer', $csv);
        $this->assertStringContainsString('Kinerja Editor', $csv);
        $this->assertStringContainsString('Rp 500.000', $csv);
    }

    /**
     * Pengujian: ekspor laporan PDF oleh Manajer.
     * Hasil yang diharapkan: tampilan cetak laporan berisi metadata dan tabel kinerja kru.
     */
    public function test_pdf_export_returns_printable_report_view(): void
    {
        [$manager, $category] = $this->seedReportScenario();

        $response = $this->actingAs($manager)->get(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
            'download' => 'pdf',
        ]));

        $response->assertOk()
            ->assertSee('Laporan Kinerja Kru Alterstudio', false)
            ->assertSee('Alter Studio', false)
            ->assertSee('Diekspor Oleh', false)
            ->assertSee($manager->name, false)
            ->assertSee('Kinerja Fotografer', false)
            ->assertSee('Kinerja Editor', false);
    }

    /** Kegagalan membentuk CSV harus dikembalikan sebagai pesan yang dapat dipahami pengguna. */
    public function test_csv_export_failure_redirects_with_error_message(): void
    {
        [$manager, $category] = $this->seedReportScenario();
        $controller = Mockery::mock(ReportController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('buildCsv')
            ->once()
            ->andThrow(new \RuntimeException('Simulasi kegagalan CSV.'));
        $this->app->instance(ReportController::class, $controller);

        $filters = [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
        ];

        $this->actingAs($manager)
            ->get(route('reports.index', [...$filters, 'download' => 'csv']))
            ->assertRedirect(route('reports.index', $filters))
            ->assertSessionHas('error', 'Gagal membuat laporan CSV. Silakan coba kembali.');
    }

    /** Kegagalan merender PDF harus dikembalikan sebagai pesan yang dapat dipahami pengguna. */
    public function test_pdf_export_failure_redirects_with_error_message(): void
    {
        [$manager, $category] = $this->seedReportScenario();
        $controller = Mockery::mock(ReportController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('renderPdfReport')
            ->once()
            ->andThrow(new \RuntimeException('Simulasi kegagalan PDF.'));
        $this->app->instance(ReportController::class, $controller);

        $filters = [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
        ];

        $this->actingAs($manager)
            ->get(route('reports.index', [...$filters, 'download' => 'pdf']))
            ->assertRedirect(route('reports.index', $filters))
            ->assertSessionHas('error', 'Gagal membuat laporan PDF. Silakan coba kembali.');
    }

    /**
     * Pengujian: tampilan laporan Owner.
     * Hasil yang diharapkan: Owner melihat detail final, filter kategori diabaikan, dan tombol ekspor tidak muncul.
     */
    public function test_owner_report_contains_additional_final_detail(): void
    {
        [, $category] = $this->seedReportScenario();
        $owner = User::factory()->create(['role' => Role::OWNER, 'name' => 'Owner Alter']);

        $response = $this->actingAs($owner)->get(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'category_id' => $category->id,
        ]));

        $response->assertOk()
            ->assertViewHas('isOwnerReport', true)
            ->assertViewHas('canExportReport', false)
            ->assertViewHas('categoryId', null)
            ->assertSee('Detail Owner', false)
            ->assertSee('Ringkasan Final Pemasukan', false)
            ->assertSee('Pelunasan / Lunas', false)
            ->assertSee('Terapkan Periode', false)
            ->assertDontSee('Kategori Laporan', false)
            ->assertDontSee('Unduh CSV', false)
            ->assertDontSee('Unduh PDF', false);
    }

    /**
     * Pengujian: pembatasan ekspor laporan untuk Owner.
     * Hasil yang diharapkan: permintaan ekspor Owner dialihkan kembali ke halaman laporan tanpa file unduhan.
     */
    public function test_owner_cannot_export_report_files(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER, 'name' => 'Owner Alter']);

        $response = $this->actingAs($owner)->get(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'download' => 'csv',
        ]));

        $response->assertRedirect(route('reports.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));
    }

    private function seedReportScenario(): array
    {
        $manager = User::factory()->create(['role' => Role::MANAGER, 'name' => 'Manager Alter']);
        $client = User::factory()->create(['role' => Role::CLIENT, 'name' => 'Client Report']);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'name' => 'Fotografer A']);
        $editor = User::factory()->create(['role' => Role::EDITOR, 'name' => 'Editor A']);
        $category = ServiceCategory::factory()->create(['name' => 'Keluarga']);
        $package = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Big Family',
            'price' => 500000,
        ]);
        $location = StudioLocation::create([
            'name' => 'Cabang Laporan',
            'slug' => 'cabang-laporan',
            'address' => 'Jl. Laporan',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->toDateString(),
            'status' => Booking::STATUS_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 500000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-REPORT-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        Project::factory()->create([
            'booking_id' => $booking->id,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'status' => Project::STATUS_FINAL,
            'start_at' => now()->setTime(10, 0),
            'end_at' => now()->setTime(11, 0),
        ]);

        return [$manager, $category];
    }
}
