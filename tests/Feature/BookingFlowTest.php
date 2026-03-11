<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_creates_project_and_waiting_payment(): void
    {
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Utama',
            'slug' => 'cabang-utama',
            'address' => 'Jl. Contoh No. 1',
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $payload = [
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '13:00',
            'location' => 'Studio A',
            'notes' => 'Please be on time',
            'payment_type' => 'FULL',
        ];

        $response = $this->actingAs($client)
            ->postJson('/bookings', $payload)
            ->assertCreated();

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertEquals('WAITING_PAYMENT', $booking->status);
        $this->assertEquals($package->price, $booking->total_price);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals($booking->id, $project->booking_id);
        $this->assertEquals('DRAFT', $project->status);

        $response->assertJsonPath('project.id', $project->id);
    }
}
