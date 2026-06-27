<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemainingControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_login_hits_controller_guard(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email')
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_service_category_web_branches_are_covered(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)
            ->getJson('/admin/categories')
            ->assertOk();

        $this->actingAs($admin)
            ->from(route('admin.catalog'))
            ->post('/admin/categories', [
                'name' => 'Kategori Web',
                'description' => 'Dibuat via web',
            ])
            ->assertRedirect(route('admin.catalog'))
            ->assertSessionHas('status', 'Kategori ditambahkan.');

        $category = ServiceCategory::where('name', 'Kategori Web')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.catalog'))
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Kategori Web Update',
                'description' => 'Update via web',
            ])
            ->assertRedirect(route('admin.catalog'))
            ->assertSessionHas('status', 'Kategori diperbarui.');

        ServicePackage::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->from(route('admin.catalog'))
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect(route('admin.catalog'))
            ->assertSessionHas('error');

        $unused = ServiceCategory::factory()->create(['name' => 'Kategori Hapus Web']);

        $this->actingAs($admin)
            ->from(route('admin.catalog'))
            ->delete("/admin/categories/{$unused->id}")
            ->assertRedirect(route('admin.catalog'))
            ->assertSessionHas('status', 'Kategori dihapus.');
    }

    public function test_catalog_nested_package_image_upload_paths_are_covered(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.catalog.store'), [
                'name' => 'Kategori Nested Upload',
                'description' => 'Memiliki paket dengan gambar',
                'packages' => [
                    [
                        'name' => 'Paket Nested',
                        'price' => 123000,
                        'description' => 'Paket dari form kategori',
                        'features' => "Satu\nDua",
                        'addons' => 'Extra waktu:50000, Cetak-25000',
                        'terms' => 'Syarat paket',
                        'overview_image' => UploadedFile::fake()->image('nested-cover.jpg', 1200, 800),
                        'gallery' => [
                            UploadedFile::fake()->image('nested-1.jpg', 800, 600),
                            UploadedFile::fake()->image('nested-2.jpg', 800, 600),
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.catalog'));

        $package = ServicePackage::where('name', 'Paket Nested')->firstOrFail();
        $this->assertNotEmpty($package->gallery);
        $this->assertNotNull($package->cover_image);
        Storage::disk('public')->assertExists($package->cover_image);
    }

    public function test_media_asset_controller_edge_branches_are_covered(): void
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $otherPhotographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $otherEditor = User::factory()->create(['role' => Role::EDITOR]);

        $project = $this->paidScheduledProject($photographer, $editor);

        $this->actingAs($otherPhotographer)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'RAW',
                'raw_drive_url' => 'https://drive.google.com/wrong',
            ])
            ->assertForbidden();

        $project->update(['raw_drive_url' => 'https://drive.google.com/raw']);

        $this->actingAs($photographer)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'RAW',
                'raw_drive_url' => 'https://drive.google.com/raw-again',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Link Drive foto mentah sudah tersimpan dan tidak dapat diunggah ulang.');

        $this->actingAs($otherEditor)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'FINAL',
                'final_drive_url' => 'https://drive.google.com/final',
            ])
            ->assertForbidden();

        $this->actingAs($editor)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'FINAL',
                'final_drive_url' => 'https://drive.google.com/final',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Permintaan edit dari klien belum tersedia.');

        $project->update([
            'edit_photo_codes' => 'A01',
            'edit_request_note' => 'Edit ringan',
            'edit_requested_at' => now(),
            'final_drive_uploaded_at' => now(),
            'final_drive_url' => 'https://drive.google.com/final-existing',
        ]);

        $this->actingAs($editor)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'FINAL',
                'final_drive_url' => 'https://drive.google.com/final-again',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Hasil final sudah ditandai tersedia.');

        $noDriveProject = $this->paidScheduledProject($photographer, $editor);
        $noDriveProject->update([
            'edit_photo_codes' => 'B02',
            'edit_request_note' => 'Edit tanpa drive',
            'edit_requested_at' => now(),
        ]);

        $this->actingAs($editor)
            ->postJson(route('projects.drive-assets.store', $noDriveProject), [
                'type' => 'FINAL',
                'final_message' => 'Final tersedia via admin.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Link Drive hasil final belum tersedia.');

        $this->actingAs($noDriveProject->booking->client)
            ->from(route('bookings.show', $noDriveProject->booking))
            ->get(route('projects.raw.download', $noDriveProject))
            ->assertRedirect(route('bookings.show', $noDriveProject->booking))
            ->assertSessionHas('error', 'Link Drive foto mentah belum tersedia.');
    }

    public function test_user_management_view_filters_and_self_update_branch_are_covered(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);

        $this->actingAs($owner)
            ->get(route('admin.users.index', ['role_filter' => 'photographer']))
            ->assertOk()
            ->assertViewIs('admin.users.index');

        $this->actingAs($owner)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertViewIs('admin.users.create');

        $this->actingAs($owner)
            ->get(route('admin.users.edit', $owner))
            ->assertOk()
            ->assertViewIs('admin.users.edit');

        $this->actingAs($owner)
            ->put(route('admin.users.update', $owner), [
                'name' => 'Owner Update',
                'email' => $owner->email,
                'role' => Role::OWNER->value,
                'no_hp' => '081234567890',
                'is_active' => false,
            ])
            ->assertRedirect(route('profile.edit'));

        $owner->refresh();
        $this->assertTrue($owner->isRole(Role::OWNER));
        $this->assertTrue($owner->is_active);

        $this->actingAs($owner)
            ->put(route('admin.users.update', $photographer), [
                'name' => 'Crew Update',
                'email' => $photographer->email,
                'role' => Role::PHOTOGRAPHER->value,
                'password' => 'new-password',
                'no_hp' => '081234567891',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($photographer->fresh()->isRole(Role::PHOTOGRAPHER));
    }

    public function test_booking_controller_filters_rejections_payment_and_addon_parsing_are_covered(): void
    {
        config(['studio.closed_weekdays' => [0]]);

        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT, 'name' => 'Klien Filter']);
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create([
            'name' => 'Paket Filter',
            'duration_minutes' => 60,
            'addons' => [
                ['label' => '', 'price' => 1000],
                ['label' => 'Tambah orang Rp50k', 'price' => 0],
            ],
        ]);
        $location = StudioLocation::create([
            'name' => 'Cabang Booking Filter',
            'slug' => 'booking-filter',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Booking Filter',
            'is_active' => true,
        ]);

        $submitted = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'booking_date' => now()->addWeekday()->toDateString(),
            'booking_time' => '11:00',
        ]);
        Project::factory()->create(['booking_id' => $submitted->id, 'status' => Project::STATUS_DRAFT]);

        $scheduled = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
            'booking_date' => now()->addWeekday()->toDateString(),
            'booking_time' => '13:00',
        ]);
        Project::factory()->create([
            'booking_id' => $scheduled->id,
            'status' => Project::STATUS_SCHEDULED,
            'start_at' => now()->addWeekday()->setTime(13, 0),
            'end_at' => now()->addWeekday()->setTime(14, 0),
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/bookings?status=SUBMITTED&schedule_status=unscheduled&package_id='.$package->id.'&date_from='.now()->toDateString().'&date_to='.now()->addMonth()->toDateString().'&q=Klien')
            ->assertOk()
            ->assertJsonPath('data.0.id', $submitted->id);

        $this->actingAs($admin)
            ->getJson('/admin/bookings?status='.Booking::STATUS_PAID.'&schedule_status=scheduled&q='.$scheduled->id)
            ->assertOk()
            ->assertJsonPath('data.0.id', $scheduled->id);

        $this->actingAs($client)
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertViewIs('client.booking.index');

        $this->actingAs($client)
            ->get(route('bookings.create'))
            ->assertOk()
            ->assertViewIs('client.booking.create');

        $closedDate = now()->next(\Carbon\CarbonInterface::SUNDAY)->toDateString();
        $this->actingAs($client)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $closedDate,
                'booking_time' => '11:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors('booking_date');

        $emptyLocation = StudioLocation::create([
            'name' => 'Cabang Tanpa Ruangan',
            'slug' => 'tanpa-ruangan',
            'is_active' => true,
        ]);
        $this->actingAs($client)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'package_id' => $package->id,
                'studio_location_id' => $emptyLocation->id,
                'booking_date' => now()->addWeekday()->toDateString(),
                'booking_time' => '11:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors('booking_time');

        $this->actingAs($client)
            ->get(route('bookings.pay', $scheduled))
            ->assertOk()
            ->assertViewIs('client.booking.pay');

        $this->actingAs($otherClient)
            ->get(route('bookings.pay', $scheduled))
            ->assertForbidden();

        $cancelled = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        $this->actingAs($client)
            ->get(route('bookings.pay', $cancelled))
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('error');

        $this->actingAs($client)
            ->get(route('bookings.pay', $submitted))
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('error');

        $dpBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_DP_PAID,
            'total_price' => 1000000,
        ]);
        Payment::create([
            'booking_id' => $dpBooking->id,
            'type' => 'FULL',
            'amount' => 900000,
            'status' => 'PENDING',
        ]);

        $this->actingAs($admin)
            ->from('/admin/bookings')
            ->post(route('admin.bookings.status', $dpBooking), [
                'status' => Booking::STATUS_PAID,
            ])
            ->assertRedirect('/admin/bookings')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $dpBooking->id,
            'type' => 'FULL',
            'status' => 'PAID',
        ]);

        $controller = app(\App\Http\Controllers\BookingController::class);
        $method = new \ReflectionMethod($controller, 'normalizePackageAddons');
        $method->setAccessible(true);
        $addons = $method->invoke($controller, $package);
        $this->assertSame('Tambah orang', array_values($addons)[0]['label']);
        $this->assertSame(50000, array_values($addons)[0]['price']);

        $parse = new \ReflectionMethod($controller, 'parseAddonLabelAndPrice');
        $parse->setAccessible(true);
        $this->assertSame(['Tambah waktu', 125000], $parse->invoke($controller, 'Tambah waktu - 125.000'));
        $this->assertSame(['Tambah properti', 50000], $parse->invoke($controller, 'Tambah properti Rp50k'));
        $this->assertSame(['Tanpa harga', 0], $parse->invoke($controller, 'Tanpa harga'));
    }

    public function test_payment_expired_zero_remaining_dashboard_profile_and_auth_views_are_covered(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 500000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Payment Edge',
            'slug' => 'payment-edge',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Payment Edge',
            'is_active' => true,
        ]);

        $expiredBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subHour(),
            'payment_started_at' => now()->subMinutes(31),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
        ]);
        Payment::create([
            'booking_id' => $expiredBooking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $expiredBooking), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Waktu pembayaran 30 menit sudah habis. Pemesanan dibatalkan otomatis, silakan pesan ulang.');

        $zeroRemaining = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_DP_PAID,
            'total_price' => 500000,
            'payment_type' => Booking::PAYMENT_TYPE_DP,
        ]);
        Payment::create([
            'booking_id' => $zeroRemaining->id,
            'type' => Payment::TYPE_DP,
            'amount' => 500000,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $zeroRemaining), [
                'type' => Payment::TYPE_FULL,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Sisa pembayaran sudah tidak ada.');

        $expiredConfirm = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now()->subHour(),
            'payment_started_at' => now()->subMinutes(31),
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
        ]);
        Payment::create([
            'booking_id' => $expiredConfirm->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $expiredConfirm))
            ->assertStatus(422)
            ->assertJsonPath('booking_status', Booking::STATUS_CANCELLED);

        foreach ([Role::ADMIN, Role::MANAGER, Role::OWNER, Role::PHOTOGRAPHER, Role::EDITOR] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'email_verified_at' => now(),
            ]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertViewIs('dashboard');
        }

        $profileUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($profileUser)
            ->get(route('profile.form'))
            ->assertOk()
            ->assertViewIs('profile.form')
            ->assertViewHas('readOnly', false);

        $this->actingAs($profileUser)
            ->get(route('profile.password'))
            ->assertOk()
            ->assertViewIs('profile.password');
    }

    public function test_media_asset_json_success_paths_are_covered(): void
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $project = $this->paidScheduledProject($photographer, $editor);

        $this->actingAs($photographer)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'RAW',
                'raw_drive_url' => 'https://drive.google.com/raw-success',
            ])
            ->assertOk()
            ->assertJsonPath('raw_drive_url', 'https://drive.google.com/raw-success');

        $project->refresh()->update([
            'edit_photo_codes' => 'C01',
            'edit_request_note' => 'Edit success',
            'edit_requested_at' => now(),
            'status' => Project::STATUS_EDITING,
        ]);

        $this->actingAs($editor)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'FINAL',
                'final_message' => 'Selesai',
            ])
            ->assertOk()
            ->assertJsonPath('final_drive_url', 'https://drive.google.com/raw-success')
            ->assertJsonPath('status', Project::STATUS_FINAL);
    }

    public function test_cleanup_role_photoselection_report_and_model_edge_paths_are_covered(): void
    {
        Storage::fake('public');

        $oldActiveClient = User::factory()->create([
            'role' => Role::CLIENT,
            'created_at' => now()->subMonths(7),
        ]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Cleanup',
            'slug' => 'cleanup',
            'is_active' => true,
        ]);
        $activeBooking = Booking::factory()->create([
            'client_id' => $oldActiveClient->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
            'created_at' => now()->subMonths(7),
        ]);
        Project::factory()->create([
            'booking_id' => $activeBooking->id,
            'status' => Project::STATUS_SCHEDULED,
        ]);

        $this->artisan('clients:cleanup-inactive --dry-run')
            ->expectsOutput('Akun klien tidak aktif yang diproses: 0')
            ->assertExitCode(0);

        $oldReferencedClient = User::factory()->create([
            'role' => Role::CLIENT,
            'created_at' => now()->subMonths(7),
            'avatar_path' => 'avatars/old-client.jpg',
        ]);
        Storage::disk('public')->put('avatars/old-client.jpg', 'avatar');
        DB::table('password_reset_tokens')->insert([
            'email' => $oldReferencedClient->email,
            'token' => 'token',
            'created_at' => now(),
        ]);
        $historicBooking = Booking::factory()->create([
            'client_id' => $oldReferencedClient->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_CANCELLED,
            'created_at' => now()->subMonths(7),
        ]);
        Project::factory()->create([
            'booking_id' => $historicBooking->id,
            'status' => Project::STATUS_FINAL,
            'raw_drive_uploaded_by' => $oldReferencedClient->id,
        ]);

        $this->artisan('clients:cleanup-inactive')
            ->expectsOutput('Akun klien tidak aktif yang diproses: 1')
            ->assertExitCode(0);

        $oldReferencedClient->refresh();
        $this->assertFalse($oldReferencedClient->is_active);
        $this->assertNull($oldReferencedClient->avatar_path);
        Storage::disk('public')->assertMissing('avatars/old-client.jpg');
        $this->assertDatabaseMissing('password_reset_tokens', ['user_id' => $oldReferencedClient->id]);

        $middleware = new \App\Http\Middleware\RoleMiddleware();
        $request = \Illuminate\Http\Request::create('/admin-only');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request, fn () => response('ok'), 'ADMIN');
    }

    public function test_report_csv_empty_owner_and_reflection_helpers_are_covered(): void
    {
        $report = app(\App\Http\Controllers\ReportController::class);
        $buildCsv = new \ReflectionMethod($report, 'buildCsv');
        $buildCsv->setAccessible(true);
        $csv = $buildCsv->invoke(
            $report,
            now()->subDay()->toDateString(),
            now()->toDateString(),
            'Semua Kategori',
            now(),
            'Owner',
            collect(),
            collect(),
            collect(),
            0,
            0,
            0,
            0,
            0,
            true,
            collect(),
            collect(),
            'Laporan Kosong'
        );

        $this->assertStringContainsString('Belum ada pembayaran berhasil', $csv);
        $this->assertStringContainsString('Belum ada pemesanan pada periode ini', $csv);
        $this->assertStringContainsString('Belum ada data fotografer pada periode ini', $csv);
        $this->assertStringContainsString('Belum ada data editor pada periode ini', $csv);

        $packageController = app(\App\Http\Controllers\Admin\ServicePackageController::class);
        $cleanedGallery = new \ReflectionMethod($packageController, 'cleanedGallery');
        $cleanedGallery->setAccessible(true);

        $this->assertSame(
            ['a.jpg', 'b.jpg'],
            $cleanedGallery->invoke($packageController, ['a.jpg', ['ignored' => 'b.jpg'], null, ['bad' => null]])
        );

        $project = new Project([
            'raw_drive_uploaded_by' => 1,
            'final_drive_uploaded_by' => 2,
        ]);

        $this->assertSame('raw_drive_uploaded_by', $project->rawDriveUploader()->getForeignKeyName());
        $this->assertSame('final_drive_uploaded_by', $project->finalDriveUploader()->getForeignKeyName());
    }

    private function paidScheduledProject(User $photographer, User $editor): Project
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['price' => 350000]);
        $location = StudioLocation::create([
            'name' => 'Cabang Media '.uniqid(),
            'slug' => 'media-'.uniqid(),
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Media '.uniqid(),
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
        ]);

        return Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(10, 0),
            'end_at' => now()->addDay()->setTime(11, 0),
        ]);
    }
}
