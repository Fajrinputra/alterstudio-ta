<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder demo lengkap — mencakup semua role, status booking, dan tahap produksi.
 *
 * Jalankan: php artisan db:seed --class=DemoScenarioSeeder
 */
class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedLocations();
        $this->seedHeroSlides();
        (new InitialDataSeeder())->run();
        $this->seedDemoScenarios();
    }

    // ─── USERS ─────────────────────────────────────────────────────────────────

    protected function seedUsers(): void
    {
        $users = [
            ['name' => 'Owner Alter',    'email' => 'owner@alter.test',   'role' => 'OWNER',        'no_hp' => '08110000000'],
            ['name' => 'Admin Alter',    'email' => 'admin@alter.test',   'role' => 'ADMIN',        'no_hp' => '08110000001'],
            ['name' => 'Manager Alter',  'email' => 'manager@alter.test', 'role' => 'MANAGER',      'no_hp' => '08110000002'],
            ['name' => 'Rafi Fotografer','email' => 'photo@alter.test',   'role' => 'PHOTOGRAPHER', 'no_hp' => '08110000003'],
            ['name' => 'Dewi Editor',    'email' => 'editor@alter.test',  'role' => 'EDITOR',       'no_hp' => '08110000004'],
            ['name' => 'Budi Santoso',   'email' => 'client@alter.test',  'role' => 'CLIENT',       'no_hp' => '08110000005'],
            ['name' => 'Siti Aminah',    'email' => 'client2@alter.test', 'role' => 'CLIENT',       'no_hp' => '08110000006'],
            ['name' => 'Andi Wijaya',    'email' => 'client3@alter.test', 'role' => 'CLIENT',       'no_hp' => '08110000007'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data + [
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );
        }

        $this->command->info('✓ Users seeded (8 akun)');
    }

    // ─── LOCATIONS ─────────────────────────────────────────────────────────────

    protected function seedLocations(): void
    {
        $loc1 = StudioLocation::firstOrCreate(
            ['name' => 'Signature by Alter'],
            [
                'slug'        => 'signature-by-alter',
                'description' => 'Studio utama dengan desain elegan dan berbagai pilihan background premium.',
                'map_url'     => 'https://maps.app.goo.gl/ZPfMbxaEsyTRVZTg9',
                'is_active'   => true,
            ]
        );

        foreach (['Studio Utama', 'Studio Mini', 'Outdoor Rooftop'] as $roomName) {
            StudioRoom::firstOrCreate(
                ['studio_location_code' => $loc1->location_code, 'name' => $roomName],
                ['is_active' => true]
            );
        }

        $loc2 = StudioLocation::firstOrCreate(
            ['name' => 'Casa De Alter'],
            [
                'slug'        => 'casa-de-alter',
                'description' => 'Konsep rumah dengan nuansa warm dan natural, cocok untuk keluarga dan maternity.',
                'map_url'     => 'https://maps.app.goo.gl/j4UgwRtTC7nxDeEL7',
                'is_active'   => true,
            ]
        );

        foreach (['Ruang Keluarga', 'Garden Room'] as $roomName) {
            StudioRoom::firstOrCreate(
                ['studio_location_code' => $loc2->location_code, 'name' => $roomName],
                ['is_active' => true]
            );
        }

        $this->command->info('✓ Locations & rooms seeded (2 lokasi, 5 ruangan)');
    }

    // ─── HERO SLIDES ───────────────────────────────────────────────────────────

    protected function seedHeroSlides(): void
    {
        $admin = User::where('email', 'admin@alter.test')->first();

        $slides = [
            ['title' => 'Abadikan Momen Berharga',      'eyebrow' => 'Studio Profesional', 'subtitle' => 'Studio foto profesional dengan hasil berkualitas tinggi', 'image_path' => '', 'is_active' => true, 'sort_order' => 1],
            ['title' => 'Foto Keluarga Penuh Kenangan', 'eyebrow' => 'Sesi Keluarga',       'subtitle' => 'Kami hadir untuk merekam setiap cerita keluarga Anda',   'image_path' => '', 'is_active' => true, 'sort_order' => 2],
            ['title' => 'Sesi Wisuda Berkesan',          'eyebrow' => 'Paket Wisuda',        'subtitle' => 'Rayakan pencapaian terbaik dengan foto yang memukau',    'image_path' => '', 'is_active' => true, 'sort_order' => 3],
        ];

        foreach ($slides as $slide) {
            LandingHeroSlide::firstOrCreate(
                ['title' => $slide['title']],
                $slide + ['user_id' => $admin?->id]
            );
        }

        $this->command->info('✓ Hero slides seeded (3 slide)');
    }

    // ─── DEMO SCENARIOS ────────────────────────────────────────────────────────

    protected function seedDemoScenarios(): void
    {
        $admin        = User::where('email', 'admin@alter.test')->first();
        $photographer = User::where('email', 'photo@alter.test')->first();
        $editor       = User::where('email', 'editor@alter.test')->first();
        $client1      = User::where('email', 'client@alter.test')->first();
        $client2      = User::where('email', 'client2@alter.test')->first();
        $client3      = User::where('email', 'client3@alter.test')->first();

        $loc1  = StudioLocation::where('name', 'Signature by Alter')->first();
        $room1 = StudioRoom::where('studio_location_code', $loc1->location_code)->where('name', 'Studio Utama')->first();
        $room2 = StudioRoom::where('studio_location_code', $loc1->location_code)->where('name', 'Studio Mini')->first();
        $loc2  = StudioLocation::where('name', 'Casa De Alter')->first();
        $room3 = StudioRoom::where('studio_location_code', $loc2->location_code)->where('name', 'Ruang Keluarga')->first();

        $pkgFamily     = ServicePackage::where('name', 'Family')->first()     ?? ServicePackage::first();
        $pkgPersonal   = ServicePackage::where('name', 'Paket Personal')->first() ?? ServicePackage::first();
        $pkgGraduation = ServicePackage::where('name', 'Paket II')
            ->whereHas('category', fn ($q) => $q->where('name', 'Graduation'))->first() ?? ServicePackage::first();

        // ── Skenario 1: Booking baru, belum dikonfirmasi admin ──
        $b1 = Booking::firstOrCreate(
            ['client_id' => $client1->id, 'package_id' => $pkgPersonal->id, 'booking_date' => Carbon::tomorrow()->setTime(10, 0)],
            [
                'status'               => Booking::STATUS_WAITING_PAYMENT,
                'payment_type'         => Booking::PAYMENT_TYPE_DP,
                'booking_time'         => '10:00',
                'studio_location_code' => $loc1->location_code,
                'studio_room_code'     => $room1->room_code,
                'total_price'          => (int) ($pkgPersonal->price ?? 350000),
                'addon_total'          => 0,
                'notes'                => 'Demo: Booking baru belum dikonfirmasi admin.',
                'confirmed_at'         => null,
            ]
        );
        $this->command->info("  → Booking #{$b1->id}: WAITING_PAYMENT (belum dikonfirmasi)");

        // ── Skenario 2: Dikonfirmasi, menunggu pembayaran DP ──
        $b2 = Booking::firstOrCreate(
            ['client_id' => $client2->id, 'package_id' => $pkgGraduation->id, 'booking_date' => Carbon::tomorrow()->setTime(13, 0)],
            [
                'status'               => Booking::STATUS_WAITING_PAYMENT,
                'payment_type'         => Booking::PAYMENT_TYPE_DP,
                'booking_time'         => '13:00',
                'studio_location_code' => $loc1->location_code,
                'studio_room_code'     => $room2->room_code,
                'total_price'          => (int) ($pkgGraduation->price ?? 750000),
                'addon_total'          => 0,
                'notes'                => 'Demo: Sudah dikonfirmasi, menunggu pembayaran DP.',
                'confirmed_at'         => now(),
                'payment_started_at'   => now(),
            ]
        );
        $this->command->info("  → Booking #{$b2->id}: WAITING_PAYMENT (dikonfirmasi, siap bayar DP)");

        // ── Skenario 3: DP dibayar, project DRAFT, siap dijadwalkan ──
        $b3 = Booking::firstOrCreate(
            ['client_id' => $client3->id, 'package_id' => $pkgFamily->id, 'booking_date' => Carbon::now()->addDays(3)->setTime(9, 0)],
            [
                'status'               => Booking::STATUS_DP_PAID,
                'payment_type'         => Booking::PAYMENT_TYPE_DP,
                'booking_time'         => '09:00',
                'studio_location_code' => $loc2->location_code,
                'studio_room_code'     => $room3->room_code,
                'total_price'          => (int) ($pkgFamily->price ?? 1500000),
                'addon_total'          => 0,
                'notes'                => 'Demo: DP dibayar, admin perlu menjadwalkan kru.',
                'confirmed_at'         => now()->subDay(),
                'payment_started_at'   => now()->subDay(),
            ]
        );
        Payment::firstOrCreate(['booking_id' => $b3->id, 'type' => Payment::TYPE_DP], [
            'amount' => (int) ($b3->total_price * 0.1), 'status' => Payment::STATUS_PAID,
            'order_id' => 'DEMO-DP-'.$b3->id, 'transaction_status' => 'settlement', 'paid_at' => now()->subDay(),
        ]);
        $p3 = Project::firstOrCreate(['booking_id' => $b3->id], ['status' => Project::STATUS_DRAFT]);
        $this->command->info("  → Booking #{$b3->id}: DP_PAID, Project #{$p3->id}: DRAFT");

        // ── Skenario 4: Lunas, SCHEDULED, kru terjadwal ──
        $b4 = Booking::firstOrCreate(
            ['client_id' => $client1->id, 'package_id' => $pkgFamily->id, 'booking_date' => Carbon::now()->addDays(2)->setTime(14, 0)],
            [
                'status'               => Booking::STATUS_PAID,
                'payment_type'         => Booking::PAYMENT_TYPE_FULL,
                'booking_time'         => '14:00',
                'studio_location_code' => $loc1->location_code,
                'studio_room_code'     => $room1->room_code,
                'total_price'          => (int) ($pkgFamily->price ?? 1500000),
                'addon_total'          => 0,
                'notes'                => 'Demo: Lunas, kru sudah dijadwalkan.',
                'confirmed_at'         => now()->subDays(2),
                'payment_started_at'   => now()->subDays(2),
            ]
        );
        Payment::firstOrCreate(['booking_id' => $b4->id, 'type' => Payment::TYPE_FULL], [
            'amount' => (int) $b4->total_price, 'status' => Payment::STATUS_PAID,
            'order_id' => 'DEMO-FULL-'.$b4->id, 'transaction_status' => 'settlement', 'paid_at' => now()->subDays(2),
        ]);
        $p4 = Project::firstOrCreate(['booking_id' => $b4->id], [
            'status' => Project::STATUS_SCHEDULED,
            'start_at' => Carbon::now()->addDays(2)->setTime(14, 0),
            'end_at'   => Carbon::now()->addDays(2)->setTime(15, 30),
        ]);
        ProjectSchedule::firstOrCreate(['project_id' => $p4->id], [
            'booking_id'           => $b4->id,
            'studio_location_code' => $loc1->location_code,
            'studio_room_code'     => $room1->room_code,
            'scheduled_by'         => $admin->id,
            'photographer_id'      => $photographer->id,
            'editor_id'            => $editor->id,
            'start_at'             => Carbon::now()->addDays(2)->setTime(14, 0),
            'end_at'               => Carbon::now()->addDays(2)->setTime(15, 30),
            'status'               => ProjectSchedule::STATUS_SCHEDULED,
        ]);
        $this->command->info("  → Booking #{$b4->id}: PAID, Project #{$p4->id}: SCHEDULED");

        // ── Skenario 5: Sesi selesai, link RAW tersedia (SHOOT_DONE) ──
        $b5 = Booking::firstOrCreate(
            ['client_id' => $client2->id, 'package_id' => $pkgPersonal->id, 'booking_date' => Carbon::now()->subDay()->setTime(10, 0)],
            [
                'status'               => Booking::STATUS_PAID,
                'payment_type'         => Booking::PAYMENT_TYPE_FULL,
                'booking_time'         => '10:00',
                'studio_location_code' => $loc1->location_code,
                'studio_room_code'     => $room2->room_code,
                'total_price'          => (int) ($pkgPersonal->price ?? 350000),
                'addon_total'          => 0,
                'confirmed_at'         => now()->subDays(3), 'payment_started_at' => now()->subDays(3),
            ]
        );
        Payment::firstOrCreate(['booking_id' => $b5->id, 'type' => Payment::TYPE_FULL], [
            'amount' => (int) $b5->total_price, 'status' => Payment::STATUS_PAID,
            'order_id' => 'DEMO-RAW-'.$b5->id, 'transaction_status' => 'settlement', 'paid_at' => now()->subDays(3),
        ]);
        $p5 = Project::firstOrCreate(['booking_id' => $b5->id], [
            'status'                => Project::STATUS_SHOOT_DONE,
            'start_at'              => now()->subDay()->setTime(10, 0),
            'end_at'                => now()->subDay()->setTime(11, 0),
            'raw_drive_url'         => 'https://drive.google.com/drive/folders/demo-raw-folder',
            'raw_drive_uploaded_by' => $photographer->id,
            'raw_drive_uploaded_at' => now()->subHours(2),
        ]);
        ProjectSchedule::firstOrCreate(['project_id' => $p5->id], [
            'booking_id' => $b5->id, 'studio_location_code' => $loc1->location_code,
            'studio_room_code' => $room2->room_code, 'scheduled_by' => $admin->id,
            'photographer_id' => $photographer->id, 'editor_id' => $editor->id,
            'start_at' => now()->subDay()->setTime(10, 0), 'end_at' => now()->subDay()->setTime(11, 0),
            'status' => ProjectSchedule::STATUS_SCHEDULED,
        ]);
        $this->command->info("  → Booking #{$b5->id}: PAID, Project #{$p5->id}: SHOOT_DONE (link RAW tersedia)");

        // ── Skenario 6: Klien kirim permintaan edit (EDITING) ──
        $b6 = Booking::firstOrCreate(
            ['client_id' => $client3->id, 'package_id' => $pkgGraduation->id, 'booking_date' => Carbon::now()->subDays(4)->setTime(9, 0)],
            [
                'status'               => Booking::STATUS_PAID,
                'payment_type'         => Booking::PAYMENT_TYPE_FULL,
                'booking_time'         => '09:00',
                'studio_location_code' => $loc2->location_code,
                'studio_room_code'     => $room3->room_code,
                'total_price'          => (int) ($pkgGraduation->price ?? 750000),
                'addon_total'          => 0,
                'confirmed_at'         => now()->subDays(6), 'payment_started_at' => now()->subDays(6),
            ]
        );
        Payment::firstOrCreate(['booking_id' => $b6->id, 'type' => Payment::TYPE_FULL], [
            'amount' => (int) $b6->total_price, 'status' => Payment::STATUS_PAID,
            'order_id' => 'DEMO-EDIT-'.$b6->id, 'transaction_status' => 'settlement', 'paid_at' => now()->subDays(6),
        ]);
        $p6 = Project::firstOrCreate(['booking_id' => $b6->id], [
            'status'                => Project::STATUS_EDITING,
            'selections_locked'     => true,
            'start_at'              => now()->subDays(4)->setTime(9, 0),
            'end_at'                => now()->subDays(4)->setTime(10, 0),
            'raw_drive_url'         => 'https://drive.google.com/drive/folders/demo-raw-graduation',
            'raw_drive_uploaded_by' => $photographer->id,
            'raw_drive_uploaded_at' => now()->subDays(3),
            'edit_photo_codes'      => "D001, D005, D012\nD018, D023",
            'edit_request_note'     => 'Tolong tone hangat, kurangi highlight, dan hapus background yang tidak rapi.',
            'edit_requested_at'     => now()->subDays(2),
        ]);
        ProjectSchedule::firstOrCreate(['project_id' => $p6->id], [
            'booking_id' => $b6->id, 'studio_location_code' => $loc2->location_code,
            'studio_room_code' => $room3->room_code, 'scheduled_by' => $admin->id,
            'photographer_id' => $photographer->id, 'editor_id' => $editor->id,
            'start_at' => now()->subDays(4)->setTime(9, 0), 'end_at' => now()->subDays(4)->setTime(10, 0),
            'status' => ProjectSchedule::STATUS_SCHEDULED,
        ]);
        $this->command->info("  → Booking #{$b6->id}: PAID, Project #{$p6->id}: EDITING");

        // ── Skenario 7: Hasil final siap (FINAL) ──
        $b7 = Booking::firstOrCreate(
            ['client_id' => $client1->id, 'package_id' => $pkgGraduation->id, 'booking_date' => Carbon::now()->subWeek()->setTime(10, 0)],
            [
                'status'               => Booking::STATUS_PAID,
                'payment_type'         => Booking::PAYMENT_TYPE_FULL,
                'booking_time'         => '10:00',
                'studio_location_code' => $loc1->location_code,
                'studio_room_code'     => $room1->room_code,
                'total_price'          => (int) ($pkgGraduation->price ?? 750000),
                'addon_total'          => 0,
                'confirmed_at'         => now()->subWeek()->subDay(), 'payment_started_at' => now()->subWeek()->subDay(),
            ]
        );
        Payment::firstOrCreate(['booking_id' => $b7->id, 'type' => Payment::TYPE_FULL], [
            'amount' => (int) $b7->total_price, 'status' => Payment::STATUS_PAID,
            'order_id' => 'DEMO-FINAL-'.$b7->id, 'transaction_status' => 'settlement', 'paid_at' => now()->subWeek()->subDay(),
        ]);
        $p7 = Project::firstOrCreate(['booking_id' => $b7->id], [
            'status'                  => Project::STATUS_FINAL,
            'selections_locked'       => true,
            'start_at'                => now()->subWeek()->setTime(10, 0),
            'end_at'                  => now()->subWeek()->setTime(11, 0),
            'raw_drive_url'           => 'https://drive.google.com/drive/folders/demo-raw-final',
            'raw_drive_uploaded_by'   => $photographer->id,
            'raw_drive_uploaded_at'   => now()->subDays(6),
            'edit_photo_codes'        => "G001, G003, G007",
            'edit_request_note'       => 'Tone natural, sedikit preset retro.',
            'edit_requested_at'       => now()->subDays(5),
            'final_drive_url'         => 'https://drive.google.com/drive/folders/demo-final-result',
            'final_message'           => 'Foto sudah siap. Silakan unduh sebelum 3 hari ke depan.',
            'final_drive_uploaded_by' => $editor->id,
            'final_drive_uploaded_at' => now()->subDays(2),
        ]);
        ProjectSchedule::firstOrCreate(['project_id' => $p7->id], [
            'booking_id' => $b7->id, 'studio_location_code' => $loc1->location_code,
            'studio_room_code' => $room1->room_code, 'scheduled_by' => $admin->id,
            'photographer_id' => $photographer->id, 'editor_id' => $editor->id,
            'start_at' => now()->subWeek()->setTime(10, 0), 'end_at' => now()->subWeek()->setTime(11, 0),
            'status' => ProjectSchedule::STATUS_SCHEDULED,
        ]);
        $this->command->info("  → Booking #{$b7->id}: PAID, Project #{$p7->id}: FINAL");

        // ── Skenario 8: Booking dibatalkan ──
        Booking::firstOrCreate(
            ['client_id' => $client2->id, 'package_id' => $pkgPersonal->id, 'booking_date' => Carbon::now()->subDays(10)->setTime(11, 0)],
            [
                'status' => Booking::STATUS_CANCELLED, 'payment_type' => Booking::PAYMENT_TYPE_DP,
                'booking_time' => '11:00', 'studio_location_code' => $loc1->location_code,
                'studio_room_code' => $room1->room_code,
                'total_price' => (int) ($pkgPersonal->price ?? 350000), 'addon_total' => 0,
                'notes' => 'Demo: Booking dibatalkan.', 'confirmed_at' => null,
            ]
        );
        $this->command->info("  → Booking CANCELLED (demo pembatalan)");

        $this->command->newLine();
        $this->command->info('✅ Demo scenarios seeded!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['OWNER',        'owner@alter.test',   'password'],
                ['ADMIN',        'admin@alter.test',   'password'],
                ['MANAGER',      'manager@alter.test', 'password'],
                ['PHOTOGRAPHER', 'photo@alter.test',   'password'],
                ['EDITOR',       'editor@alter.test',  'password'],
                ['CLIENT 1',     'client@alter.test',  'password'],
                ['CLIENT 2',     'client2@alter.test', 'password'],
                ['CLIENT 3',     'client3@alter.test', 'password'],
            ]
        );
    }
}

