<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Role;

/**
 * Factory booking untuk kebutuhan test/seed ringan.
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
        // Reuse data existing agar tidak membuat relasi duplikat berlebihan.
        $package = ServicePackage::first() ?? ServicePackage::factory()->create();
        $client = User::first() ?? User::factory()->create(['role' => Role::CLIENT]);

        return [
            'client_id' => $client->id,
            'package_id' => $package->id,
            'booking_date' => now()->addDays(1),
            'location' => fake()->address(),
            'notes' => fake()->sentence(),
            'status' => 'WAITING_PAYMENT',
            'total_price' => $package->price,
        ];
    }
}
