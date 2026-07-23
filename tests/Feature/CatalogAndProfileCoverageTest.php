<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\Admin\CatalogController;
use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class CatalogAndProfileCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_pages_and_package_creation_paths_are_covered(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $category = ServiceCategory::create(['name' => 'Wisuda', 'description' => 'Kategori wisuda']);
        $package = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Paket A',
            'gallery' => [['path' => 'legacy.jpg']],
        ]);

        $this->actingAs($admin)->get(route('admin.catalog'))->assertOk();
        $this->actingAs($admin)->get(route('catalog.public'))->assertOk();
        $this->actingAs($admin)->get(route('catalog.package.show', $package))->assertOk();
        $this->actingAs($admin)->get(route('admin.catalog.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.catalog.packages', $category))->assertOk();
        $this->actingAs($admin)->get(route('admin.catalog.packages.create', $category))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.catalog.store'), [
                'name' => 'Keluarga',
                'description' => 'Kategori keluarga',
                'packages' => [
                    [
                        'name' => 'Paket Keluarga',
                        'price' => 750000,
                        'description' => 'Deskripsi',
                        'features' => "Foto indoor\nCetak",
                        'addons' => 'Tambah waktu:100000, Frame|50000',
                        'terms' => 'Syarat',
                    ],
                    [
                        'description' => 'Paket kosong dilewati',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.catalog'));

        $this->assertDatabaseHas('service_categories', ['name' => 'Keluarga']);
        $this->assertDatabaseHas('service_packages', ['name' => 'Paket Keluarga']);

        $this->actingAs($admin)
            ->post(route('admin.catalog.packages.store', $category), [
                'name' => 'Paket Upload',
                'price' => 900000,
                'max_people' => 4,
                'description' => 'Dengan gambar',
                'features' => "Makeup\nCetak",
                'addons' => [
                    ['label' => 'Tambah waktu', 'price' => 100000, 'unit' => 'sesi'],
                    ['label' => '', 'price' => 100000],
                ],
                'terms' => 'Tidak refund',
                'is_active' => '1',
                'overview_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
                'gallery' => [
                    UploadedFile::fake()->image('gallery-a.jpg', 1200, 800),
                    UploadedFile::fake()->image('gallery-b.jpg', 1200, 800),
                ],
            ])
            ->assertRedirect(route('admin.catalog.packages', $category));

        $stored = ServicePackage::where('name', 'Paket Upload')->firstOrFail();
        $this->assertNotNull($stored->cover_image);
        $this->assertNotEmpty($stored->gallery);
    }

    public function test_catalog_controller_normalizer_helpers_cover_array_string_and_fallback_paths(): void
    {
        $controller = app(CatalogController::class);

        $this->assertSame(['A', 'B'], $this->invoke($controller, 'toArray', [[' A ', '', 'B'], ',']));
        $this->assertSame(['A', 'B'], $this->invoke($controller, 'toArray', [' A, B ,,', ',']));
        $this->assertSame([], $this->invoke($controller, 'toArray', [null, ',']));
        $this->assertSame([
            ['label' => 'Addon', 'price' => 0, 'unit' => ''],
        ], $this->invoke($controller, 'normalizeAddons', [[['label' => ' Addon ', 'price' => -5, 'unit' => '']]]));
        $this->assertSame([
            ['label' => 'Label', 'price' => 0, 'unit' => ''],
        ], $this->invoke($controller, 'normalizeCsvAddons', ['Label']));
        $this->assertSame(['Raw', 12000], $this->invoke($controller, 'parseAddonLabelAndPrice', ['Raw-12.000']));
    }

    public function test_profile_avatar_upload_replace_delete_and_empty_update_are_covered(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($user)
            ->post(route('profile.avatar'), [
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'avatar-updated');

        $firstPath = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($user)
            ->post(route('profile.avatar'), [
                'avatar' => UploadedFile::fake()->image('avatar-2.jpg', 300, 300),
            ])
            ->assertRedirect();

        Storage::disk('public')->assertMissing($firstPath);
        $secondPath = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($secondPath);

        $this->actingAs($user)
            ->post(route('profile.avatar.delete'))
            ->assertRedirect()
            ->assertSessionHas('status', 'avatar-updated');

        Storage::disk('public')->assertMissing($secondPath);
        $this->assertNull($user->fresh()->avatar_path);

        $this->actingAs($user)
            ->post(route('profile.avatar'))
            ->assertRedirect()
            ->assertSessionHas('status', 'avatar-updated');
    }

    public function test_simple_model_relationships_are_covered(): void
    {
        $creator = User::factory()->create(['role' => Role::ADMIN]);
        $slide = LandingHeroSlide::create([
            'eyebrow' => 'Alter',
            'title' => 'Hero',
            'subtitle' => 'Subtitle',
            'image_path' => 'hero.jpg',
            'sort_order' => '2',
            'is_active' => 1,
            'user_id' => $creator->id,
        ]);

        $location = StudioLocation::create(['name' => 'Cabang Relasi', 'slug' => 'relasi', 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_code' => $location->location_code, 'name' => 'Studio Relasi', 'is_active' => true]);
        $package = ServicePackage::factory()->create();
        $booking = Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);
        $project = Project::factory()->create(['booking_id' => $booking->id]);
        $uploader = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $media = MediaAsset::create([
            'project_id' => $project->id,
            'type' => MediaAsset::TYPE_RAW,
            'path' => 'drive-link',
            'uploaded_by' => $uploader->id,
            'version' => '3',
            'expires_at' => now()->addDays(7),
        ]);
        $selection = PhotoSelection::create([
            'project_id' => $project->id,
            'media_code' => $media->media_code,
            'client_id' => $booking->client_id,
            'selected_at' => now(),
        ]);

        $this->assertTrue($slide->is_active);
        $this->assertSame(2, $slide->sort_order);
        $this->assertTrue($slide->user->is($creator));
        $this->assertSame([MediaAsset::TYPE_RAW, MediaAsset::TYPE_FINAL], MediaAsset::TYPES);
        $this->assertSame(3, $media->version);
        $this->assertTrue($media->project->is($project));
        $this->assertTrue($media->uploader->is($uploader));
        $this->assertTrue($selection->project->is($project));
        $this->assertTrue($selection->client->is($booking->client));
        $this->assertTrue($selection->mediaAsset->is($media));
        $this->assertTrue($room->location->is($location));
        $this->assertTrue($room->bookings->first()->is($booking));
    }

    private function invoke(object $object, string $method, array $parameters): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $parameters);
    }
}
