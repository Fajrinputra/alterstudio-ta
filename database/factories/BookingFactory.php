<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Role;

/**
 * Factory booking untuk kebutuhan test/seed ringan.
 *
 * Menggunakan kolom code (varchar) bukan integer id untuk:
 *  - studio_location_code → studio_locations.location_code
 *  - studio_room_code     → studio_rooms.room_code
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $package  = ServicePackage::first() ?? ServicePackage::factory()->create();
        $client   = User::first() ?? User::factory()->create(['role' => Role::CLIENT]);
        $location = StudioLocation::first() ?? StudioLocation::create([
            'name'      => 'Cabang Test',
            'slug'      => 'cabang-test-' . uniqid(),
            'is_active' => true,
        ]);
        $room = StudioRoom::where('studio_location_code', $location->location_code)->first()
            ?? StudioRoom::create([
                'studio_location_code' => $location->location_code,
                'name'                 => 'Studio Test',
                'is_active'            => true,
            ]);

        return [
            'client_id'            => $client->id,
            'package_id'           => $package->id,
            'booking_date'         => now()->addDays(1),
            'notes'                => fake()->sentence(),
            'status'               => 'WAITING_PAYMENT',
            'payment_type'         => 'FULL',
            'studio_location_code' => $location->location_code,
            'studio_room_code'     => $room->room_code,
            'total_price'          => $package->price,
        ];
    }
}
