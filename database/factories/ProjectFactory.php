<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory project sebagai turunan langsung dari booking.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $booking = Booking::first() ?? Booking::factory()->create();

        return [
            'booking_id' => $booking->id,
            'status' => 'DRAFT',
        ];
    }
}
