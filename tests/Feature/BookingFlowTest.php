<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\BookingController;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fokus 1 - Menampilkan daftar pemesanan berdasarkan role pengguna.
     * Hasil yang diharapkan: klien hanya melihat booking miliknya,
     * sedangkan admin dapat melihat daftar booking operasional.
     */
    public function test_booking_index_filters_bookings_by_user_role(): void
    {
        $package = ServicePackage::factory()->create(['is_active' => true]);
        $location = StudioLocation::create([
            'name' => 'Cabang Index',
            'slug' => 'cabang-index',
            'address' => 'Jl. Index No. 1',
            'is_active' => true,
        ]);
        $clientA = User::factory()->create(['role' => Role::CLIENT]);
        $clientB = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        Booking::factory()->create([
            'client_id' => $clientA->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);
        Booking::factory()->create([
            'client_id' => $clientB->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);

        $this->actingAs($clientA)
            ->getJson(route('bookings.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.client_id', $clientA->id);

        $this->actingAs($admin)
            ->getJson('/admin/bookings?status=SUBMITTED')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /**
     * Fokus 2 - Menampilkan formulir pemesanan klien.
     * Hasil yang diharapkan: form memuat paket aktif, cabang aktif,
     * pilihan add-on paket, dan batas tanggal maksimal satu bulan.
     */
    public function test_create_booking_form_shows_active_package_context(): void
    {
        $package = ServicePackage::factory()->create([
            'is_active' => true,
            'addons' => [
                ['label' => 'Tambah waktu', 'price' => 100000, 'unit' => 'sesi'],
            ],
        ]);
        StudioLocation::create([
            'name' => 'Cabang Form',
            'slug' => 'cabang-form',
            'address' => 'Jl. Form No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($client)
            ->get(route('bookings.create', ['package_id' => $package->id]))
            ->assertOk()
            ->assertViewHas('selectedPackage', fn ($selected) => $selected?->id === $package->id)
            ->assertViewHas('addonOptions', fn ($addons) => count($addons) === 1)
            ->assertViewHas('maxBookingDate', Carbon::today()->addMonth()->toDateString());
    }

    /**
     * Fokus 5 - Menyimpan pemesanan baru dari klien.
     * Hasil yang diharapkan: booking tersimpan sebagai pengajuan, belum dikonfirmasi admin,
     * mendapat ruangan tersedia, dan project awal dibuat dengan status DRAFT.
     */
    public function test_booking_creation_creates_project_and_marks_booking_as_submitted(): void
    {
        config()->set('studio.closed_weekdays', []);

        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Utama',
            'slug' => 'cabang-utama',
            'address' => 'Jl. Contoh No. 1',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $payload = [
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => Carbon::now()->addDays(2)->toDateString(),
            'booking_time' => '13:00',
            'location' => 'Studio A',
            'notes' => 'Please be on time',
            'payment_type' => 'FULL',
        ];

        $response = $this->actingAs($client)
            ->postJson('/bookings', $payload)
            ->assertCreated();

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertEquals('WAITING_PAYMENT', $booking->status);
        $this->assertNull($booking->confirmed_at);
        $this->assertEquals($package->price, $booking->total_price);
        $this->assertEquals($room->id, $booking->studio_room_id);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals($booking->id, $project->booking_id);
        $this->assertEquals('DRAFT', $project->status);

        $response->assertJsonPath('project.id', $project->id);
        $response->assertJsonPath('display_status', 'Diajukan');
    }

    /**
     * Fokus 7 - Menampilkan detail pemesanan.
     * Hasil yang diharapkan: klien hanya dapat melihat detail booking miliknya sendiri.
     */
    public function test_client_can_only_show_own_booking_detail(): void
    {
        $package = ServicePackage::factory()->create(['is_active' => true]);
        $location = StudioLocation::create([
            'name' => 'Cabang Detail',
            'slug' => 'cabang-detail',
            'address' => 'Jl. Detail No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
        ]);
        Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_DRAFT,
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 150000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($client)
            ->getJson(route('bookings.show', $booking))
            ->assertOk()
            ->assertJsonPath('id', $booking->id)
            ->assertJsonStructure(['package', 'project', 'payments']);

        $this->actingAs($otherClient)
            ->getJson(route('bookings.show', $booking))
            ->assertForbidden();
    }

    /**
     * Fokus 13 - Menolak pembayaran sebelum konfirmasi admin atau setelah booking batal.
     * Hasil yang diharapkan: sistem menolak pembayaran pada booking yang masih berstatus diajukan.
     */
    public function test_submitted_booking_cannot_be_paid_before_admin_confirmation(): void
    {
        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Utara',
            'slug' => 'cabang-utara',
            'address' => 'Jl. Utara No. 2',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 350000,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan masih menunggu konfirmasi admin. Pembayaran belum dapat dilakukan.');
    }

    /**
     * Fokus 8 - Mengonfirmasi pemesanan oleh admin.
     * Hasil yang diharapkan: field confirmed_at terisi dan booking masuk kondisi siap dibayar.
     */
    public function test_admin_can_confirm_submitted_booking_before_payment(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 420000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Tengah',
            'slug' => 'cabang-tengah-2',
            'address' => 'Jl. Tengah No. 1',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 420000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_WAITING_PAYMENT,
            ])
            ->assertRedirect();

        $this->assertNotNull($booking->fresh()->confirmed_at);
        $this->assertTrue($booking->fresh()->isConfirmedAwaitingPayment());
    }

    /**
     * Fokus 9 - Membatalkan pemesanan yang masih valid untuk dibatalkan.
     * Hasil yang diharapkan: booking diajukan dapat dibatalkan dan pembayaran pending ditandai gagal.
     */
    public function test_admin_can_cancel_submitted_booking_and_fail_pending_payment(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 300000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Batal',
            'slug' => 'cabang-batal',
            'address' => 'Jl. Batal No. 1',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 300000,
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 300000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status pemesanan diperbarui.');

        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_FAILED, $booking->payments()->first()->status);
    }

    /**
     * Fokus 12 - Menampilkan halaman pembayaran klien.
     * Hasil yang diharapkan: payment_started_at terisi saat halaman pembayaran pertama kali dibuka.
     */
    public function test_payment_timeout_starts_when_client_opens_payment_page(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Timer',
            'slug' => 'cabang-timer',
            'address' => 'Jl. Timer No. 5',
            'is_active' => true,
        ]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subMinutes(10),
            'payment_started_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 500000,
        ]);

        $this->actingAs($client)
            ->get(route('bookings.pay', $booking))
            ->assertOk();

        $this->assertNotNull($booking->fresh()->payment_started_at);
    }

    /**
     * Fokus 14 - Membatalkan otomatis booking yang melewati batas waktu pembayaran.
     * Hasil yang diharapkan: booking menjadi CANCELLED dan pembayaran pending menjadi EXPIRED.
     */
    public function test_pay_page_cancels_booking_when_payment_window_is_expired(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Expired',
            'slug' => 'cabang-expired',
            'address' => 'Jl. Expired No. 1',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subHour(),
            'payment_started_at' => now()->subMinutes(31),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 500000,
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($client)
            ->get(route('bookings.pay', $booking))
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('error', 'Waktu pembayaran 30 menit sudah habis. Pemesanan dibatalkan otomatis, silakan pesan ulang.');

        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $booking->payments()->first()->status);
    }

    /**
     * Pendukung Fokus 10 - Menguji pelunasan setelah klien sebelumnya membayar DP 10%.
     * Hasil yang diharapkan: sistem membuat transaksi pelunasan sebesar sisa pembayaran.
     */
    public function test_dp_paid_booking_can_create_full_settlement_snap_for_remaining_balance(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-settlement',
            ]),
        ]);

        config()->set('services.midtrans.server_key', 'test-server-key');
        config()->set('services.midtrans.sandbox', true);

        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Tengah',
            'slug' => 'cabang-tengah',
            'address' => 'Jl. Tengah No. 10',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 350000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 35000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertOk();

        $response->assertJsonPath('amount', 315000);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 315000,
            'status' => Payment::STATUS_PENDING,
            'snap_token' => 'snap-token-settlement',
        ]);
    }

    /**
     * Pendukung Fokus 10 - Menguji kegagalan transaksi pelunasan pada booking yang sudah membayar DP.
     * Hasil yang diharapkan: status booking tetap DP_PAID dan hanya transaksi pelunasan yang kedaluwarsa.
     */
    public function test_failed_settlement_does_not_remove_existing_dp_paid_status(): void
    {
        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Timur',
            'slug' => 'cabang-timur',
            'address' => 'Jl. Timur No. 20',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 350000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 35000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-2',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $settlement = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 315000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-FULL-FAIL',
            'snap_token' => 'snap-fail',
        ]);

        $this->postJson('/midtrans/webhook', [
            'order_id' => $settlement->order_id,
            'transaction_status' => 'expire',
        ])->assertOk();

        $this->assertEquals(Booking::STATUS_DP_PAID, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $settlement->fresh()->status);
    }

    /**
     * Fokus 10 - Memproses pelunasan manual setelah DP.
     * Hasil yang diharapkan: booking menjadi PAID dan sistem mencatat pembayaran pelunasan di lokasi.
     */
    public function test_admin_can_mark_dp_paid_booking_as_paid_for_onsite_settlement(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $package = ServicePackage::factory()->create(['price' => 1000000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Pelunasan',
            'slug' => 'cabang-pelunasan',
            'address' => 'Jl. Pelunasan No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 1000000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-ONSITE',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_PAID,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status pemesanan diperbarui.');

        $this->assertEquals(Booking::STATUS_PAID, $booking->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 900000,
            'status' => Payment::STATUS_PAID,
            'reference' => 'manual_onsite_settlement',
            'transaction_status' => 'manual',
        ]);
    }

    /**
     * Fokus 11 - Menolak perubahan status yang tidak valid.
     * Hasil yang diharapkan: booking DP_PAID tidak dapat dibatalkan manual oleh admin.
     */
    public function test_dp_paid_booking_cannot_be_cancelled_manually(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $package = ServicePackage::factory()->create(['price' => 1000000]);
        $location = StudioLocation::create([
            'name' => 'Cabang DP Aman',
            'slug' => 'cabang-dp-aman',
            'address' => 'Jl. DP No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 1000000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_DP,
            'amount' => 100000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-DP-NO-CANCEL',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Perubahan status tidak valid untuk kondisi pemesanan saat ini.');

        $this->assertEquals(Booking::STATUS_DP_PAID, $booking->fresh()->status);
        $this->assertDatabaseMissing('payments', [
            'booking_id' => $booking->id,
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    /**
     * Fokus 11 - Menolak perubahan status yang tidak valid.
     * Hasil yang diharapkan: booking PAID tidak dapat diubah kembali menjadi dibatalkan.
     */
    public function test_paid_booking_status_cannot_be_changed_manually(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $package = ServicePackage::factory()->create(['price' => 450000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Selatan',
            'slug' => 'cabang-selatan',
            'address' => 'Jl. Selatan No. 9',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
            'confirmed_at' => now()->subHour(),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 450000,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 450000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'ORDER-PAID-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect();

        $this->assertEquals(Booking::STATUS_PAID, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_PAID, $booking->payments()->first()->status);
    }

    /**
     * Pendukung Fokus 13 - Menguji kegagalan pembayaran awal sebelum ada pembayaran yang berhasil.
     * Hasil yang diharapkan: booking tetap menunggu pembayaran dan transaksi ditandai gagal.
     */
    public function test_failed_initial_payment_returns_booking_to_waiting_payment(): void
    {
        $package = ServicePackage::factory()->create(['price' => 280000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Barat',
            'slug' => 'cabang-barat',
            'address' => 'Jl. Barat No. 7',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'total_price' => 280000,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 280000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-FULL-DENY',
            'snap_token' => 'snap-deny',
        ]);

        $this->postJson('/midtrans/webhook', [
            'order_id' => $payment->order_id,
            'transaction_status' => 'deny',
        ])->assertOk();

        $this->assertEquals(Booking::STATUS_WAITING_PAYMENT, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_FAILED, $payment->fresh()->status);
    }

    /**
     * Fokus 15 - Menormalisasi add-on paket.
     * Hasil yang diharapkan: add-on paket menjadi struktur stabil dan harga dapat dibaca dari label.
     */
    public function test_booking_controller_normalizes_package_addons(): void
    {
        $package = ServicePackage::factory()->make();
        $package->setRawAttributes([
            'addons' => json_encode([
                ['label' => ' Tambah waktu ', 'price' => 100000, 'unit' => 'sesi'],
                ['label' => 'Cetak 10R 25k', 'price' => 0, 'unit' => 'lembar'],
                ['label' => '', 'price' => 99999],
            ]),
        ], true);

        $addons = $this->invokeBookingControllerMethod('normalizePackageAddons', [$package]);
        [$label, $price] = $this->invokeBookingControllerMethod('parseAddonLabelAndPrice', ['Frame:50000']);

        $this->assertCount(2, $addons);
        $this->assertSame('Tambah waktu', array_values($addons)[0]['label']);
        $this->assertSame(100000, array_values($addons)[0]['price']);
        $this->assertSame('Frame', $label);
        $this->assertSame(50000, $price);
    }

    /**
     * Fokus 16 - Membentuk add-on pilihan dari request.
     * Hasil yang diharapkan: hanya add-on valid yang diproses dan subtotal dihitung dari harga serta kuantitas.
     */
    public function test_booking_controller_builds_chosen_addons_from_request(): void
    {
        $package = ServicePackage::factory()->make();
        $package->setRawAttributes([
            'addons' => json_encode([
                ['label' => 'Tambah waktu', 'price' => 100000, 'unit' => 'sesi'],
            ]),
        ], true);
        $addonKey = md5('Tambah waktu|100000');

        $chosenAddons = $this->invokeBookingControllerMethod('chosenAddonsFromRequest', [
            $package,
            [
                'selected_addons' => [$addonKey, 'invalid-key'],
                'addon_quantities' => [$addonKey => 2],
            ],
        ]);

        $this->assertCount(1, $chosenAddons);
        $this->assertSame('Tambah waktu', $chosenAddons[0]['label']);
        $this->assertSame(2, $chosenAddons[0]['quantity']);
        $this->assertSame(200000, $chosenAddons[0]['subtotal']);
    }

    /**
     * Fokus 17 - Menyimpan add-on terpilih pada booking.
     * Hasil yang diharapkan: selected_addons tersimpan dalam format label, harga, unit, kuantitas, dan subtotal.
     */
    public function test_booking_controller_syncs_selected_addons_to_booking(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Addon',
            'slug' => 'cabang-addon',
            'address' => 'Jl. Addon No. 1',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'total_price' => 500000,
        ]);

        $this->invokeBookingControllerMethod('syncBookingAddons', [
            $booking,
            [
                [
                    'label' => ' Tambah waktu ',
                    'price' => 100000,
                    'unit' => 'sesi',
                    'quantity' => 2,
                ],
            ],
        ]);

        $savedAddons = $booking->fresh()->selected_addons;

        $this->assertSame('Tambah waktu', $savedAddons[0]['label']);
        $this->assertSame(100000, $savedAddons[0]['price']);
        $this->assertSame(2, $savedAddons[0]['quantity']);
        $this->assertSame(200000, $savedAddons[0]['subtotal']);
    }

    private function invokeBookingControllerMethod(string $method, array $parameters = []): mixed
    {
        $controller = app(BookingController::class);
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($controller, $parameters);
    }
}
