<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;

/**
 * Seeder data awal user + katalog layanan default.
 */
class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Urutan: user lebih dulu, lalu master layanan.
        $this->seedUsers();
        $this->seedServices();
    }

    protected function seedUsers(): void
    {
        // Akun demo untuk masing-masing role.
        $users = [
            ['name' => 'Admin Alter', 'email' => 'admin@alter.test', 'role' => 'ADMIN', 'no_hp' => '08110000001'],
            ['name' => 'Manager Alter', 'email' => 'manager@alter.test', 'role' => 'MANAGER', 'no_hp' => '08110000002'],
            ['name' => 'Photographer One', 'email' => 'photo@alter.test', 'role' => 'PHOTOGRAPHER', 'no_hp' => '08110000003'],
            ['name' => 'Editor One', 'email' => 'editor@alter.test', 'role' => 'EDITOR', 'no_hp' => '08110000004'],
            ['name' => 'Client Demo', 'email' => 'client@alter.test', 'role' => 'CLIENT', 'no_hp' => '08110000005'],
        ];

        foreach ($users as $data) {
            User::factory()->create(array_merge($data, ['password' => 'password']));
        }
    }

    protected function seedServices(): void
    {
        // Dataset katalog awal untuk memudahkan demo/testing.
        $data = [
            'Pas Photo & Postcard' => [
                ['name'=>'Paket Pasphoto', 'price'=>75000, 'description'=>'Close-up (kepala sampai dada)', 'features'=>['1 kostum','5-6 take','Cetak 2x3 (5 lbr)','3x4 (5 lbr)','4x6 (5 lbr)'], 'addons'=>['Ganti kostum Rp25k','Tambah background Rp25k','Edit tambahan Rp25k'], 'terms'=>'Booking dengan DP; DP non-refundable; 5-6 take foto'],
                ['name'=>'Paket Postcard', 'price'=>75000, 'description'=>'Full body', 'features'=>['1 kostum','5-6 take','Cetak 3R (2 lbr)'], 'addons'=>['Ganti kostum Rp25k','Tambah background Rp25k','Edit tambahan Rp25k'], 'terms'=>'Booking dengan DP; DP non-refundable; 5-6 take foto'],
            ],
            'Personal' => [
                ['name'=>'Paket Personal', 'price'=>350000, 'description'=>'Fokus individu, 20 menit', 'features'=>['1 kostum','1 background','3 foto edit','file only','all file'], 'addons'=>['Ganti kostum Rp150k','Tambah background Rp50k','Tambah waktu Rp150k/10m','Edit tambahan Rp50k'], 'terms'=>'Berpedoman pada durasi waktu'],
                ['name'=>'Biograph I', 'price'=>700000, 'description'=>'30 menit', 'features'=>['1 kostum','3 background','6 foto edit','file only','all file'], 'addons'=>['Ganti kostum Rp150k','Tambah background Rp50k','Tambah waktu Rp150k/10m','Edit tambahan Rp50k'], 'terms'=>'Berpedoman pada durasi waktu'],
                ['name'=>'Biograph II', 'price'=>1800000, 'description'=>'50 menit', 'features'=>['2 kostum','5 background','10 foto edit','Square Magazine 20x20 (10 lbr)','Cetak 20R + Bingkai','all file'], 'addons'=>['Ganti kostum Rp150k','Tambah background Rp50k','Tambah waktu Rp150k/10m','Edit tambahan Rp50k'], 'terms'=>'Berpedoman pada durasi waktu'],
            ],
            'Group' => [
                ['name'=>'Paket 2-4 Orang', 'price'=>85000, 'description'=>'15 menit', 'features'=>['2 kostum & 2 background','Cetak 4R (2 lbr/orang) atau 10R (1 lbr/orang)'], 'addons'=>['Sesi personal Rp50k/orang','Tambah waktu Rp100k/10m','Ganti kostum Rp150k','Tambah background Rp50k'], 'terms'=>'Kombinasi foto harus wajar agar sesi teratur'],
                ['name'=>'Paket 5-10 Orang', 'price'=>75000, 'description'=>'30 menit', 'features'=>['2 kostum & 2 background','Cetak 4R (2 lbr/orang) atau 10R (1 lbr/orang)'], 'addons'=>['Sesi personal Rp50k/orang','Tambah waktu Rp100k/10m','Ganti kostum Rp150k','Tambah background Rp50k'], 'terms'=>'Kombinasi foto harus wajar agar sesi teratur'],
                ['name'=>'Paket 11-19 Orang', 'price'=>60000, 'description'=>'45 menit', 'features'=>['2 kostum & 2 background','Cetak 4R (2 lbr/orang) atau 10R (1 lbr/orang)'], 'addons'=>['Sesi personal Rp50k/orang','Tambah waktu Rp100k/10m','Ganti kostum Rp150k','Tambah background Rp50k'], 'terms'=>'Kombinasi foto harus wajar agar sesi teratur'],
                ['name'=>'>20 Orang', 'price'=>50000, 'description'=>'90 menit', 'features'=>['2 kostum & 2 background','Cetak 4R (2 lbr/orang) atau 10R (1 lbr/orang)'], 'addons'=>['Sesi personal Rp50k/orang','Tambah waktu Rp100k/10m','Ganti kostum Rp150k','Tambah background Rp50k'], 'terms'=>'Kombinasi foto harus wajar agar sesi teratur'],
            ],
            'Family' => [
                ['name'=>'Mini Family', 'price'=>950000, 'description'=>'30 menit, maks 8 orang', 'features'=>['1 kostum','3 background','7 foto edit','Cetak 20R + Bingkai','Cetak 16R + Bingkai'], 'addons'=>['Tambah orang Rp50k','Tambah waktu Rp100k/10m','Ganti kostum Rp50k'], 'max_people'=>8],
                ['name'=>'Family', 'price'=>1500000, 'description'=>'45 menit, maks 10 orang', 'features'=>['2 kostum','4 background','10 foto edit','Cetak 20R + Bingkai','16R + Bingkai','10R + Bingkai (3)'], 'addons'=>['Tambah orang Rp50k','Tambah waktu Rp100k/10m','Ganti kostum Rp50k'], 'max_people'=>10],
                ['name'=>'Big Family', 'price'=>3000000, 'description'=>'90 menit, maks 20 orang', 'features'=>['3 kostum','Semua background 1 studio','18 foto edit','Cetak 24R + Bingkai','16R + Bingkai (2)','10R + Bingkai (5)'], 'addons'=>['Tambah orang Rp50k','Tambah waktu Rp100k/10m','Ganti kostum Rp50k'], 'max_people'=>20],
            ],
            'Graduation' => [
                ['name'=>'Paket I', 'price'=>500000, 'description'=>'25 menit, maks 6 orang', 'features'=>['2 background','5 edit','file only'], 'terms'=>'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas'],
                ['name'=>'Paket II', 'price'=>750000, 'description'=>'30 menit, maks 8 orang', 'features'=>['2 background','7 edit','Cetak 20R + Bingkai'], 'terms'=>'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas'],
                ['name'=>'Paket III', 'price'=>950000, 'description'=>'30 menit, maks 15 orang', 'features'=>['3 background','9 edit','Cetak 16R + Bingkai','Cetak 10R + Bingkai'], 'terms'=>'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas'],
                ['name'=>'Paket IV', 'price'=>1300000, 'description'=>'45 menit, maks 20 orang', 'features'=>['4 background','12 edit','Cetak 20R','16R','Kolase 4R + Bingkai'], 'terms'=>'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas'],
                ['name'=>'Paket V', 'price'=>2700000, 'description'=>'60 menit, maks 25 orang', 'features'=>['Semua background','20 edit','Cetak 24R','16R (2)','Kolase 4R'], 'terms'=>'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas'],
            ],
            'Lainnya' => [
                ['name'=>'Maternity 45m', 'price'=>900000, 'description'=>'45 menit', 'features'=>[], 'addons'=>[]],
                ['name'=>'Maternity 60m', 'price'=>1500000, 'description'=>'60 menit', 'features'=>[], 'addons'=>[]],
                ['name'=>'Baby 1-3 th (tanpa dekor)', 'price'=>500000, 'description'=>'', 'features'=>[], 'addons'=>[]],
                ['name'=>'Baby 1-3 th (dengan orang tua)', 'price'=>750000, 'description'=>'', 'features'=>[], 'addons'=>[]],
                ['name'=>'Mini Soulmate', 'price'=>650000, 'description'=>'Couple 25 menit, 1 kostum, 1 background', 'features'=>[], 'addons'=>[]],
                ['name'=>'Pas Photo Couple', 'price'=>250000, 'description'=>'Sesi formal & non-formal 10 menit', 'features'=>[], 'addons'=>[]],
                ['name'=>'Catalogue', 'price'=>150000, 'description'=>'Per item, 5 angle, 10-15 menit, 3 edit', 'features'=>[], 'addons'=>[]],
                ['name'=>'Ijazah Session', 'price'=>150000, 'description'=>'Mulai 150k-250k termasuk makeup & kostum', 'features'=>[], 'addons'=>[]],
            ],
        ];

        foreach ($data as $categoryName => $packages) {
            $cat = \App\Models\ServiceCategory::firstOrCreate(['name' => $categoryName], ['description' => $categoryName]);
            foreach ($packages as $pkg) {
                $features = $pkg['features'] ?? [];
                $addons = $pkg['addons'] ?? [];

                unset($pkg['features'], $pkg['addons']);
                $pkg['category_id'] = $cat->id;

                \App\Models\ServicePackage::updateOrCreate(
                    ['name' => $pkg['name']],
                    $pkg + [
                        'features' => array_values($features),
                        'addons' => array_values(array_map(function ($addon) {
                            if (is_array($addon)) {
                                return [
                                    'label' => (string) ($addon['label'] ?? ''),
                                    'price' => (int) ($addon['price'] ?? 0),
                                    'is_active' => (bool) ($addon['is_active'] ?? true),
                                ];
                            }

                            return [
                                'label' => (string) $addon,
                                'price' => 0,
                                'is_active' => true,
                            ];
                        }, $addons)),
                        'gallery' => [],
                    ]
                );
            }
        }
    }

    /**
     * Seeder lokasi contoh (saat ini belum dipanggil di run()).
     */
    protected function seedLocations(): void
    {
        $locations = [
            ['name' => 'Signature by Alter', 'map_url' => 'https://maps.app.goo.gl/ZPfMbxaEsyTRVZTg9', 'city' => 'Jakarta', 'is_active' => true],
            ['name' => 'Casa De Alter', 'map_url' => 'https://maps.app.goo.gl/j4UgwRtTC7nxDeEL7', 'city' => 'Jakarta', 'is_active' => true],
        ];

        foreach ($locations as $loc) {
            \App\Models\StudioLocation::firstOrCreate(['name' => $loc['name']], $loc);
        }
    }

}
