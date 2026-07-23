<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicePackageControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_read_package_json_and_views(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $category = ServiceCategory::factory()->create(['name' => 'Kategori Paket']);
        $package = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Paket View',
            'gallery' => [
                ['path' => 'packages/1/gallery/a.jpg'],
                'packages/1/gallery/b.jpg',
                ['url' => 'packages/1/gallery/c.jpg'],
            ],
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/packages')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Paket View']);

        $this->actingAs($admin)
            ->get(route('admin.packages.show', $package))
            ->assertOk()
            ->assertViewIs('admin.catalog.package-show')
            ->assertViewHas('package', $package);

        $this->actingAs($admin)
            ->get(route('admin.packages.edit', $package))
            ->assertOk()
            ->assertViewIs('admin.catalog.package-edit')
            ->assertViewHas('package', $package);
    }

    public function test_package_store_and_update_cover_gallery_through_web_requests(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $category = ServiceCategory::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.catalog.packages', $category))
            ->post(route('admin.packages.store'), [
                'category_id' => $category->id,
                'name' => 'Paket File',
                'price' => 900000,
                'max_people' => 3,
                'duration_minutes' => 50,
                'description' => 'Paket dengan file',
                'features' => ['Cetak', '', 'Soft file'],
                'terms' => 'Tidak terlambat.',
                'is_active' => true,
                'addons' => [
                    ['label' => 'Extra waktu', 'price' => 75000, 'unit' => '10 menit'],
                    ['label' => '', 'price' => 1, 'unit' => 'invalid'],
                ],
                'overview_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
                'gallery' => [
                    UploadedFile::fake()->image('g1.jpg', 800, 600),
                    UploadedFile::fake()->image('g2.jpg', 800, 600),
                ],
            ])
            ->assertRedirect();

        $package = ServicePackage::where('name', 'Paket File')->firstOrFail();
        $this->assertNotNull($package->cover_image);
        $this->assertCount(3, $package->gallery);
        Storage::disk('public')->assertExists($package->cover_image);

        $oldCover = $package->cover_image;
        $oldGallery = $package->gallery[1];

        $this->actingAs($admin)
            ->from(route('admin.packages.edit', $package))
            ->put(route('admin.packages.update', $package), [
                'category_id' => $category->id,
                'name' => 'Paket File Update',
                'price' => 950000,
                'max_people' => 4,
                'duration_minutes' => 70,
                'description' => 'Paket file update',
                'features' => "Outdoor\r\nIndoor",
                'terms' => 'Ketentuan update.',
                'is_active' => false,
                'remove_overview' => true,
                'remove_gallery' => [$oldGallery, 'missing/path.jpg'],
                'addons' => [
                    ['label' => 'Cetak tambahan', 'price' => 50000, 'unit' => 'lembar'],
                ],
                'overview_image' => UploadedFile::fake()->image('cover-new.jpg', 1200, 800),
                'gallery' => [
                    UploadedFile::fake()->image('g3.jpg', 800, 600),
                ],
            ])
            ->assertRedirect(route('admin.catalog.packages', $category));

        $package->refresh();
        $this->assertSame('Paket File Update', $package->name);
        $this->assertFalse($package->is_active);
        $this->assertSame(['Outdoor', 'Indoor'], $package->features);
        $this->assertNotSame($oldCover, $package->cover_image);
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertMissing($oldGallery);
        Storage::disk('public')->assertExists($package->cover_image);
    }

    public function test_package_destroy_web_paths_for_active_and_unused_packages(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $location = StudioLocation::create(['name' => 'Cabang Paket Delete', 'slug' => 'paket-delete', 'is_active' => true]);

        $activePackage = ServicePackage::factory()->create(['is_active' => true]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $activePackage->id,
            'studio_location_code' => $location->location_code,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::factory()->create(['booking_id' => $booking->id, 'status' => Project::STATUS_SCHEDULED]);

        $this->actingAs($admin)
            ->from(route('admin.packages.show', $activePackage))
            ->delete(route('admin.packages.destroy', $activePackage))
            ->assertRedirect(route('admin.packages.show', $activePackage))
            ->assertSessionHas('error');

        $this->assertFalse($activePackage->fresh()->is_active);

        $unusedPackage = ServicePackage::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.packages.show', $unusedPackage))
            ->delete(route('admin.packages.destroy', $unusedPackage))
            ->assertRedirect(route('admin.packages.show', $unusedPackage))
            ->assertSessionHas('status', 'Paket dihapus.');

        $this->assertSoftDeleted('service_packages', ['id' => $unusedPackage->id]);
    }
}
