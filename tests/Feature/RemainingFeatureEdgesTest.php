<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use App\Support\BookingAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemainingFeatureEdgesTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_selection_json_and_forbidden_edges_are_covered(): void
    {
        [$project, $client] = $this->editableProject();
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($otherClient)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'A01',
                'edit_request_note' => 'Bukan pemilik.',
            ])
            ->assertForbidden();

        $this->actingAs($client)
            ->postJson(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'A01 A02',
                'edit_request_note' => 'Tone hangat.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Permintaan edit berhasil dikirim ke editor.');

        $this->actingAs($client)
            ->postJson(route('projects.edit-request.store', $project->fresh()), [
                'edit_photo_codes' => 'A03',
                'edit_request_note' => 'Ulang.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Permintaan edit sudah dikirim dan tidak dapat diubah.');
    }

    public function test_project_detail_forbids_other_client(): void
    {
        [$project] = $this->editableProject();
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);

        Auth::login($otherClient);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Http\Controllers\ProjectController::class)->show($project);
    }

    public function test_client_project_route_is_blocked_before_controller_by_role_middleware(): void
    {
        [$project] = $this->editableProject();
        $otherClient = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($otherClient)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_booking_availability_remaining_edges_are_covered(): void
    {
        config([
            'studio.closed_weekdays' => [],
            'studio.open_time' => '10:00',
            'studio.close_time' => '11:00',
            'studio.slot_interval_minutes' => 30,
            'studio.session_buffer_minutes' => 15,
        ]);

        $availability = app(BookingAvailability::class);
        $package = ServicePackage::factory()->create(['duration_minutes' => 90]);
        $location = StudioLocation::create([
            'name' => 'Cabang Availability Edge',
            'slug' => 'availability-edge',
            'is_active' => true,
        ]);

        $date = now()->addWeekday();

        $this->assertSame([], $availability->availableSlots($package, $location->id, $date));

        $shortPackage = ServicePackage::factory()->create(['duration_minutes' => 30]);
        $this->assertSame([], $availability->availableSlots($shortPackage, $location->id, $date));
        $this->assertFalse($availability->isSlotAvailable($shortPackage, $location->id, $date, '10:00'));
        $this->assertNull($availability->availableRoomForSlot($shortPackage, $location->id, $date, '09:30'));

        config(['studio.closed_weekdays' => [$date->dayOfWeek]]);
        $this->assertNull($availability->availableRoomForSlot($shortPackage, $location->id, $date, '10:00'));
        config(['studio.closed_weekdays' => []]);

        StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Room Edge',
            'is_active' => true,
        ]);

        $this->assertNull($availability->availableRoomForSlot($shortPackage, $location->id, $date, '10:15'));
    }

    public function test_remaining_service_package_controller_edges_are_covered(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $package = ServicePackage::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.packages.destroy', $package))
            ->assertOk()
            ->assertJsonPath('message', 'Paket berhasil dihapus.');

        $controller = app(\App\Http\Controllers\Admin\ServicePackageController::class);
        $toArray = new \ReflectionMethod($controller, 'toArray');
        $toArray->setAccessible(true);
        $this->assertSame([], $toArray->invoke($controller, 123, ','));
        $this->assertSame(['A', 'B'], $toArray->invoke($controller, 'A, B, ', ','));

        Storage::fake('public');
        $galleryPackage = ServicePackage::factory()->create([
            'cover_image' => 'packages/old-cover.jpg',
            'gallery' => ['packages/a.jpg', 'packages/b.jpg'],
        ]);
        Storage::disk('public')->put('packages/a.jpg', 'a');
        Storage::disk('public')->put('packages/b.jpg', 'b');

        $removeSelectedGallery = new \ReflectionMethod($controller, 'removeSelectedGallery');
        $removeSelectedGallery->setAccessible(true);
        $removeSelectedGallery->invoke(
            $controller,
            \Illuminate\Http\Request::create('/', 'POST', ['remove_gallery' => ['packages/missing.jpg']]),
            $galleryPackage
        );
        $removeSelectedGallery->invoke(
            $controller,
            \Illuminate\Http\Request::create('/', 'POST', ['remove_gallery' => ['packages/a.jpg']]),
            $galleryPackage
        );

        $galleryPackage->refresh();
        $this->assertSame(['packages/b.jpg'], $galleryPackage->gallery);
        $this->assertSame('packages/b.jpg', $galleryPackage->cover_image);

        $bookingController = app(\App\Http\Controllers\BookingController::class);
        $normalizePackageAddons = new \ReflectionMethod($bookingController, 'normalizePackageAddons');
        $normalizePackageAddons->setAccessible(true);
        $rawPackage = new class extends ServicePackage {
            public array $rawAddons = [
                ['label' => '', 'price' => 1000],
                '   ',
                'Extra Properti Rp50k',
            ];

            public function getAddonsAttribute($value): array
            {
                return $this->rawAddons;
            }
        };
        $addons = $normalizePackageAddons->invoke($bookingController, $rawPackage);
        $this->assertSame('Extra Properti', array_values($addons)[0]['label']);
        $this->assertSame(50000, array_values($addons)[0]['price']);
    }

    public function test_remaining_booking_and_schedule_edges_are_covered(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT, 'name' => 'Waiting Client']);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Remaining Booking',
            'slug' => 'remaining-booking',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Room Remaining Booking',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
        ]);
        Project::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($admin)
            ->getJson('/admin/bookings?status='.Booking::STATUS_WAITING_PAYMENT.'&q=Waiting')
            ->assertOk()
            ->assertJsonPath('data.0.id', $booking->id);

        $this->actingAs($admin)
            ->get('/admin/bookings')
            ->assertOk()
            ->assertViewIs('admin.booking.index');

        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $scheduledBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
        ]);
        $scheduled = Project::factory()->create([
            'booking_id' => $scheduledBooking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        $this->actingAs($editor)
            ->get(route('admin.schedules', ['assignment_role' => 'editor']))
            ->assertOk()
            ->assertSee((string) $scheduled->id);

        $this->actingAs($admin)
            ->get(route('admin.schedules', [
                'assignment_role' => 'photographer',
                'crew_user_id' => $photographer->id,
            ]))
            ->assertOk()
            ->assertSee((string) $scheduled->id);

        $scheduleController = app(\App\Http\Controllers\ScheduleController::class);
        $canModify = new \ReflectionMethod($scheduleController, 'canModifySchedule');
        $canModify->setAccessible(true);

        $unpaid = Project::factory()->make([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
        ]);
        $unpaid->setRelation('booking', Booking::factory()->make(['status' => Booking::STATUS_WAITING_PAYMENT]));
        $this->assertFalse($canModify->invoke($scheduleController, $unpaid));

        $locked = Project::factory()->make([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'selections_locked' => true,
        ]);
        $locked->setRelation('booking', Booking::factory()->make(['status' => Booking::STATUS_PAID]));
        $this->assertFalse($canModify->invoke($scheduleController, $locked));

        $withRaw = Project::factory()->make([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'raw_drive_url' => 'https://drive.google.com/raw',
        ]);
        $withRaw->setRelation('booking', Booking::factory()->make(['status' => Booking::STATUS_PAID]));
        $this->assertFalse($canModify->invoke($scheduleController, $withRaw));

        $buildScheduleWindow = new \ReflectionMethod($scheduleController, 'buildScheduleWindow');
        $buildScheduleWindow->setAccessible(true);
        [$start, $end] = $buildScheduleWindow->invoke($scheduleController, new Project());
        $this->assertTrue($start->equalTo($end));

        $invalidCrewProject = Project::factory()->create([
            'booking_id' => Booking::factory()->create([
                'client_id' => $client->id,
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'studio_room_id' => $room->id,
                'status' => Booking::STATUS_PAID,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '11:00',
            ])->id,
            'status' => Project::STATUS_DRAFT,
        ]);
        $wrongRole = User::factory()->create(['role' => Role::CLIENT]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);

        $this->actingAs($admin)
            ->postJson("/projects/{$invalidCrewProject->id}/schedule", [
                'photographer_id' => $wrongRole->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Akun fotografer yang dipilih tidak memiliki akses fotografer aktif.');

        $otherEditor = User::factory()->create(['role' => Role::EDITOR]);
        $overlapBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
        ]);
        $overlapProject = Project::factory()->create([
            'booking_id' => $overlapBooking->id,
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->postJson("/projects/{$overlapProject->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $otherEditor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.');

        $freeRoom = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Free Room',
            'is_active' => true,
        ]);
        $crewOverlapBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $freeRoom->id,
            'status' => Booking::STATUS_PAID,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
        ]);
        $crewOverlapProject = Project::factory()->create([
            'booking_id' => $crewOverlapBooking->id,
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->postJson("/projects/{$crewOverlapProject->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $otherEditor->id,
                'studio_room_id' => $freeRoom->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.');

        $updateBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $freeRoom->id,
            'status' => Booking::STATUS_PAID,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
        ]);
        $updateProject = Project::factory()->create([
            'booking_id' => $updateBooking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $otherEditor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        $this->actingAs($admin)
            ->putJson(route('projects.schedule.update', $updateProject), [
                'photographer_id' => $wrongRole->id,
                'editor_id' => $otherEditor->id,
                'studio_room_id' => $freeRoom->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Akun fotografer yang dipilih tidak memiliki akses fotografer aktif.');

        $this->actingAs($admin)
            ->putJson(route('projects.schedule.update', $updateProject), [
                'photographer_id' => $photographer->id,
                'editor_id' => $otherEditor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.');

        $anotherFreeRoom = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Another Free Room',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->putJson(route('projects.schedule.update', $updateProject), [
                'photographer_id' => $photographer->id,
                'editor_id' => $otherEditor->id,
                'studio_room_id' => $anotherFreeRoom->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.');
    }

    public function test_remaining_media_asset_edges_are_covered(): void
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'status' => Booking::STATUS_PAID,
        ]);
        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SHOOT_DONE,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($photographer)
            ->postJson(route('projects.drive-assets.store', $project), [
                'type' => 'RAW',
                'raw_drive_url' => 'https://drive.google.com/raw-late',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Link Drive foto mentah hanya dapat dikirim saat project berstatus Terjadwal.');

        $booking->update(['status' => Booking::STATUS_CANCELLED]);
        $project->update(['raw_drive_url' => 'https://drive.google.com/raw']);

        $this->actingAs($client)
            ->from(route('bookings.show', $booking))
            ->get(route('projects.raw.download', $project))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error', 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.');
    }

    public function test_remaining_auth_landing_and_kernel_edges_are_covered(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $this->actingAs($manager)
            ->get(route('manager.landing.hero'))
            ->assertOk()
            ->assertViewIs('admin.landing.hero');

        $verified = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($verified)
            ->get(\Illuminate\Support\Facades\URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(5),
                ['id' => $verified->id, 'hash' => sha1($verified->email)]
            ))
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        $kernel = new \App\Console\Kernel(app(), app('events'));
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

        $scheduleMethod = new \ReflectionMethod($kernel, 'schedule');
        $scheduleMethod->setAccessible(true);
        $scheduleMethod->invoke($kernel, $schedule);

        $commandsMethod = new \ReflectionMethod($kernel, 'commands');
        $commandsMethod->setAccessible(true);
        $commandsMethod->invoke($kernel);

        \Illuminate\Support\Facades\Schema::shouldReceive('hasTable')
            ->once()
            ->with('landing_hero_slides')
            ->andReturn(false);

        $landingView = app(\App\Http\Controllers\LandingController::class)();
        $this->assertSame('welcome', $landingView->name());
        $this->assertTrue($landingView->getData()['heroSlides']->isEmpty());
    }

    public function test_remaining_payment_controller_production_midtrans_urls_are_covered(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.sandbox' => false,
        ]);
        Http::fake([
            'https://app.midtrans.com/*' => Http::response(['token' => 'prod-snap-token'], 200),
            'https://api.midtrans.com/*' => Http::response(['transaction_status' => 'settlement'], 200),
        ]);

        [$project, $client] = $this->editableProject();
        $booking = $project->booking;
        $booking->update([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
            'payment_started_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), [
                'type' => 'FULL',
            ])
            ->assertOk()
            ->assertJsonPath('snap_token', 'prod-snap-token');

        $this->actingAs($client)
            ->postJson(route('bookings.pay.confirm', $booking))
            ->assertOk();
    }

    public function test_final_remaining_controller_branches_are_covered(): void
    {
        $user = User::factory()->create(['email' => 'reset-success@example.test']);
        \Illuminate\Support\Facades\Notification::fake();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\ResetPassword::class
        );

        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.sandbox' => true,
        ]);
        Http::fake([
            'https://app.sandbox.midtrans.com/*' => Http::response(['unexpected' => true], 200),
        ]);

        [$project, $client] = $this->editableProject();
        $booking = $project->booking;
        $booking->update([
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
            'payment_started_at' => null,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
        ]);

        $this->actingAs($client)
            ->postJson(route('bookings.pay.snap', $booking), ['type' => 'FULL'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Gagal membuat transaksi Midtrans');

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
            1,
            100000,
            1,
            1,
            1,
            true,
            collect([['label' => 'Lunas', 'total' => 1, 'amount' => 100000]]),
            collect([['label' => 'PAID', 'total' => 1, 'amount' => 100000]]),
            'Laporan Owner'
        );
        $this->assertStringContainsString('Lunas', $csv);
        $this->assertStringContainsString('PAID', $csv);

        $owner = User::factory()->create(['role' => Role::OWNER]);
        $target = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        Booking::factory()->create([
            'client_id' => $target->id,
            'package_id' => $package->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        $this->actingAs($owner)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => Role::CLIENT->value,
                'is_active' => false,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Akun tidak dapat dinonaktifkan karena masih memiliki pemesanan atau project yang belum selesai.');
    }

    public function test_cleanup_dry_run_processes_inactive_client_without_deleting(): void
    {
        $client = User::factory()->create([
            'role' => Role::CLIENT,
            'created_at' => now()->subMonths(7),
        ]);

        $this->artisan('clients:cleanup-inactive --dry-run')
            ->expectsOutput('Akun klien tidak aktif yang diproses: 1')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email' => 'different@example.test',
            'user_id' => $client->id,
            'token' => 'token',
            'created_at' => now(),
        ]);

        $this->artisan('clients:cleanup-inactive')
            ->expectsOutput('Akun klien tidak aktif yang diproses: 1')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('password_reset_tokens', ['user_id' => $client->id]);
    }

    /**
     * @return array{0: Project, 1: User}
     */
    private function editableProject(): array
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Edit Request '.uniqid(),
            'slug' => 'edit-request-'.uniqid(),
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio Edit Request',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SHOOT_DONE,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(10, 0),
            'end_at' => now()->addDay()->setTime(11, 0),
            'raw_drive_url' => 'https://drive.google.com/raw',
            'raw_drive_uploaded_at' => now(),
        ]);

        return [$project, $client];
    }
}
