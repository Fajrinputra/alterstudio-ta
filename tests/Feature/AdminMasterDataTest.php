<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: Admin mengelola kategori layanan yang belum digunakan.
     * Hasil yang diharapkan: kategori dapat dibuat, diperbarui, dan dihapus dari database.
     */
    public function test_admin_can_create_update_and_delete_service_category_when_unused(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)->postJson('/admin/categories', [
            'name' => 'Wisuda',
            'description' => 'Paket foto wisuda',
        ]);

        $response->assertCreated()->assertJsonPath('name', 'Wisuda');
        $category = ServiceCategory::where('name', 'Wisuda')->firstOrFail();

        $this->actingAs($admin)->putJson("/admin/categories/{$category->id}", [
            'name' => 'Graduation',
            'description' => 'Updated',
        ])->assertOk()->assertJsonPath('name', 'Graduation');

        $this->actingAs($admin)->deleteJson("/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Kategori berhasil dihapus.');

        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }

    /**
     * Pengujian: perlindungan penghapusan kategori yang masih memiliki paket.
     * Hasil yang diharapkan: sistem menolak penghapusan dan data kategori tetap tersimpan.
     */
    public function test_category_with_packages_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $category = ServiceCategory::factory()->create();
        ServicePackage::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->deleteJson("/admin/categories/{$category->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Kategori tidak bisa dihapus karena masih memiliki paket. Hapus semua paket dalam kategori ini terlebih dahulu.');

        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);
    }

    /**
     * Pengujian: Admin membuat dan memperbarui paket layanan.
     * Hasil yang diharapkan: fitur dan add-on paket tersimpan dalam format yang sudah dinormalisasi.
     */
    public function test_admin_can_create_and_update_service_package_with_normalized_features_and_addons(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $category = ServiceCategory::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.packages.store'), [
            'category_id' => $category->id,
            'name' => 'Paket Studio',
            'price' => 750000,
            'max_people' => 4,
            'duration_minutes' => 45,
            'description' => 'Sesi foto studio',
            'features' => "10 foto edit\nFile digital",
            'terms' => 'Datang tepat waktu.',
            'is_active' => true,
            'addons' => [
                ['label' => 'Tambah waktu', 'price' => 100000, 'unit' => '10 menit'],
                ['label' => '', 'price' => 999],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('name', 'Paket Studio');
        $package = ServicePackage::where('name', 'Paket Studio')->firstOrFail();

        $this->assertSame(['10 foto edit', 'File digital'], $package->features);
        $this->assertSame('Tambah waktu', $package->addons[0]['label']);
        $this->assertSame(45, $package->duration_minutes);

        $this->actingAs($admin)->putJson(route('admin.packages.update', $package), [
            'category_id' => $category->id,
            'name' => 'Paket Studio Updated',
            'price' => 850000,
            'max_people' => 5,
            'duration_minutes' => 60,
            'description' => 'Sesi foto studio updated',
            'features' => ['Cetak foto', '', 'Soft file'],
            'terms' => 'Ketentuan baru.',
            'is_active' => false,
            'addons' => [
                ['label' => 'Tambah orang', 'price' => 50000, 'unit' => 'orang'],
            ],
        ])->assertOk()->assertJsonPath('name', 'Paket Studio Updated');

        $package->refresh();
        $this->assertFalse($package->is_active);
        $this->assertSame(['Cetak foto', 'Soft file'], $package->features);
        $this->assertSame(850000, $package->price);
    }

    /**
     * Pengujian: penghapusan paket yang sudah dipakai pada booking aktif.
     * Hasil yang diharapkan: paket tidak dihapus permanen, tetapi dinonaktifkan.
     */
    public function test_package_used_by_active_booking_is_deactivated_instead_of_deleted(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['is_active' => true]);
        $location = StudioLocation::create([
            'name' => 'Cabang Aktif',
            'slug' => 'cabang-aktif',
            'address' => 'Jl. Aktif',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->deleteJson(route('admin.packages.destroy', $package))
            ->assertStatus(422);

        $this->assertFalse($package->fresh()->is_active);
        $this->assertDatabaseHas('service_packages', ['id' => $package->id]);
    }

    /**
     * Pengujian: Owner mengelola data cabang studio.
     * Hasil yang diharapkan: cabang dapat dibuat, diperbarui, dan dihapus saat belum dipakai.
     */
    public function test_owner_can_create_update_and_delete_studio_location(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $response = $this->actingAs($owner)->postJson(route('admin.locations.store'), [
            'name' => 'Cabang Selatan',
            'address' => 'Jl. Selatan No. 1',
            'description' => 'Cabang untuk sesi keluarga',
            'map_url' => 'https://maps.google.com/?q=alter',
            'is_active' => true,
        ]);

        $response->assertCreated()->assertJsonPath('name', 'Cabang Selatan');
        $location = StudioLocation::where('name', 'Cabang Selatan')->firstOrFail();
        $this->assertSame('cabang-selatan', $location->slug);

        $this->actingAs($owner)->putJson(route('admin.locations.update', $location), [
            'name' => 'Cabang Selatan Baru',
            'address' => 'Jl. Selatan No. 2',
            'description' => 'Updated',
            'map_url' => 'https://maps.google.com/?q=alter-baru',
            'is_active' => false,
        ])->assertOk()->assertJsonPath('name', 'Cabang Selatan Baru');

        $this->assertSame('cabang-selatan-baru', $location->fresh()->slug);
        $this->assertFalse($location->fresh()->is_active);

        $this->actingAs($owner)->deleteJson(route('admin.locations.destroy', $location))
            ->assertOk()
            ->assertJsonPath('message', 'Lokasi berhasil dihapus.');

        $this->assertDatabaseMissing('studio_locations', ['id' => $location->id]);
    }

    /**
     * Pengujian: Owner mengelola ruangan studio pada cabang.
     * Hasil yang diharapkan: ruangan yang sudah dipakai booking dinonaktifkan saat proses hapus.
     */
    public function test_owner_can_manage_studio_rooms_and_used_room_is_deactivated_on_delete(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $location = StudioLocation::create([
            'name' => 'Cabang Ruangan',
            'slug' => 'cabang-ruangan',
            'address' => 'Jl. Ruangan',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->post(route('admin.locations.room.store'), [
                'studio_location_id' => $location->id,
                'name' => 'Studio A',
                'description' => 'Ruangan utama',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.locations.edit', $location));

        $room = StudioRoom::where('name', 'Studio A')->firstOrFail();

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->put(route('admin.locations.room.update', $room), [
                'name' => 'Studio A Updated',
                'description' => 'Ruang utama updated',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.locations.edit', $location));

        $this->assertDatabaseHas('studio_rooms', [
            'id' => $room->id,
            'name' => 'Studio A Updated',
        ]);

        Booking::factory()->create([
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
        ]);

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->delete(route('admin.locations.room.destroy', $room))
            ->assertRedirect(route('admin.locations.edit', $location));

        $this->assertFalse($room->fresh()->is_active);
    }

    /**
     * Pengujian: Admin mengelola hero landing page.
     * Hasil yang diharapkan: slide hero dapat dibuat, diperbarui, gambar lama diganti, dan slide dihapus.
     */
    public function test_admin_can_create_update_and_delete_landing_hero_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)
            ->from(route('admin.landing.hero'))
            ->post(route('admin.landing.hero.store'), [
                'eyebrow' => 'Alter Studio',
                'title' => 'Foto Keluarga',
                'subtitle' => 'Abadikan momen keluarga',
                'sort_order' => 1,
                'is_active' => true,
                'image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
            ])
            ->assertRedirect(route('admin.landing.hero'));

        $slide = LandingHeroSlide::where('title', 'Foto Keluarga')->firstOrFail();
        Storage::disk('public')->assertExists($slide->image_path);

        $oldImage = $slide->image_path;
        $this->actingAs($admin)
            ->from(route('admin.landing.hero'))
            ->put(route('admin.landing.hero.update', $slide), [
                'eyebrow' => 'Alter',
                'title' => 'Foto Wisuda',
                'subtitle' => 'Abadikan momen wisuda',
                'sort_order' => 2,
                'is_active' => false,
                'image' => UploadedFile::fake()->image('hero-new.jpg', 1600, 900),
            ])
            ->assertRedirect(route('admin.landing.hero'));

        $slide->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($slide->image_path);
        $this->assertSame('Foto Wisuda', $slide->title);
        $this->assertFalse($slide->is_active);

        $currentImage = $slide->image_path;
        $this->actingAs($admin)
            ->from(route('admin.landing.hero'))
            ->delete(route('admin.landing.hero.destroy', $slide))
            ->assertRedirect(route('admin.landing.hero'));

        Storage::disk('public')->assertMissing($currentImage);
        $this->assertDatabaseMissing('landing_hero_slides', ['id' => $slide->id]);
    }
}
