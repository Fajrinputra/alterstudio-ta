<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudioLocationControllerCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_read_location_json_and_views(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $location = StudioLocation::create([
            'name' => 'Cabang View',
            'slug' => 'cabang-view',
            'address' => 'Jl. View',
            'is_active' => true,
        ]);
        StudioRoom::create([
            'studio_location_code' => $location->location_code,
            'name' => 'Studio View',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->getJson('/admin/locations')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Cabang View']);

        $this->actingAs($owner)
            ->get(route('admin.locations.manage'))
            ->assertOk()
            ->assertViewIs('admin.locations.index');

        $this->actingAs($owner)
            ->get(route('admin.locations.create'))
            ->assertOk()
            ->assertViewIs('admin.locations.create');

        $this->actingAs($owner)
            ->get(route('admin.locations.show', $location))
            ->assertOk()
            ->assertViewIs('admin.locations.show');

        $this->actingAs($owner)
            ->get(route('admin.locations.edit', $location))
            ->assertOk()
            ->assertViewIs('admin.locations.edit');
    }

    public function test_owner_can_store_update_and_destroy_location_with_photos(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $this->actingAs($owner)
            ->post(route('admin.locations.store'), [
                'name' => 'Cabang Foto',
                'address' => 'Jl. Foto',
                'description' => 'Cabang dengan foto',
                'map_url' => 'https://maps.google.com/?q=foto',
                'is_active' => true,
                'photos' => [
                    UploadedFile::fake()->image('loc-1.jpg', 800, 600),
                    UploadedFile::fake()->image('loc-2.jpg', 800, 600),
                ],
            ])
            ->assertRedirect();

        $location = StudioLocation::where('name', 'Cabang Foto')->firstOrFail();
        $this->assertCount(2, $location->photo_gallery);
        Storage::disk('public')->assertExists($location->photo_gallery[0]);

        $oldPhoto = $location->photo_gallery[0];

        $this->actingAs($owner)
            ->put(route('admin.locations.update', $location), [
                'name' => 'Cabang Foto Baru',
                'address' => 'Jl. Foto Baru',
                'description' => 'Cabang update',
                'map_url' => 'https://maps.google.com/?q=foto-baru',
                'is_active' => false,
                'remove_photos' => true,
                'photos' => [
                    UploadedFile::fake()->image('loc-3.jpg', 800, 600),
                ],
            ])
            ->assertRedirect(route('admin.locations.show', $location));

        $location->refresh();
        $this->assertSame('cabang-foto-baru', $location->slug);
        $this->assertFalse($location->is_active);
        $this->assertCount(1, $location->photo_gallery);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($location->photo_gallery[0]);

        $this->actingAs($owner)
            ->delete(route('admin.locations.destroy', $location))
            ->assertRedirect(route('admin.locations.manage'))
            ->assertSessionHas('status', 'Cabang berhasil dihapus.');

        $this->assertDatabaseMissing('studio_locations', ['location_code' => $location->location_code]);
    }

    public function test_owner_can_store_update_and_destroy_unused_room_with_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $location = StudioLocation::create([
            'name' => 'Cabang Ruang Foto',
            'slug' => 'ruang-foto',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->post(route('admin.locations.room.store'), [
                'studio_location_code' => $location->location_code,
                'name' => 'Studio Foto',
                'description' => 'Foto awal',
                'is_active' => true,
                'photo' => UploadedFile::fake()->image('room.jpg', 800, 600),
            ])
            ->assertRedirect(route('admin.locations.edit', $location));

        $room = StudioRoom::where('name', 'Studio Foto')->firstOrFail();
        $oldPhoto = $room->photo_path;
        Storage::disk('public')->assertExists($oldPhoto);

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->put(route('admin.locations.room.update', $room), [
                'name' => 'Studio Foto Update',
                'description' => 'Foto update',
                'is_active' => false,
                'photo' => UploadedFile::fake()->image('room-new.jpg', 800, 600),
            ])
            ->assertRedirect(route('admin.locations.edit', $location));

        $room->refresh();
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($room->photo_path);
        $this->assertFalse($room->is_active);

        $currentPhoto = $room->photo_path;

        $this->actingAs($owner)
            ->from(route('admin.locations.edit', $location))
            ->delete(route('admin.locations.room.destroy', $room))
            ->assertRedirect(route('admin.locations.edit', $location));

        Storage::disk('public')->assertMissing($currentPhoto);
        $this->assertDatabaseMissing('studio_rooms', ['room_code' => $room->room_code]);
    }

    public function test_location_with_booking_history_is_deactivated_instead_of_deleted(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $location = StudioLocation::create([
            'name' => 'Cabang Historis',
            'slug' => 'cabang-historis',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_code' => $location->location_code,
            'name' => 'Studio Historis',
            'is_active' => true,
        ]);
        Booking::factory()->create([
            'studio_location_code' => $location->location_code,
            'studio_room_code' => $room->room_code,
        ]);

        $this->actingAs($owner)
            ->delete(route('admin.locations.destroy', $location))
            ->assertRedirect(route('admin.locations.manage'))
            ->assertSessionHas('status', 'Cabang sudah digunakan pada pemesanan, sehingga dinonaktifkan untuk menjaga riwayat transaksi.');

        $this->assertDatabaseHas('studio_locations', ['location_code' => $location->location_code, 'is_active' => false]);
        $this->assertDatabaseHas('studio_rooms', ['room_code' => $room->room_code, 'is_active' => false]);
    }
}
