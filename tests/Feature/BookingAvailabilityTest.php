<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\ServicePackage;
use App\Models\StudioHoliday;
use App\Models\StudioLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_endpoint_hides_booked_slot_on_same_location(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.holidays', []);
        $bookingDate = Carbon::now()->addDays(2)->toDateString();

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $location = StudioLocation::create([
            'name' => 'Cabang Availability',
            'slug' => 'cabang-availability',
            'address' => 'Jl. Ketersediaan',
            'is_active' => true,
        ]);

        Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => $bookingDate,
            'booking_time' => '14:00',
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $bookingDate,
            ]))
            ->assertOk();

        $times = collect($response->json('available_times'))->pluck('value')->all();

        $this->assertNotContains('14:00', $times);
        $this->assertContains('13:00', $times);
        $this->assertContains('15:00', $times);
    }

    public function test_availability_endpoint_marks_weekend_as_closed(): void
    {
        config()->set('studio.closed_weekdays', [0, 6]);
        config()->set('studio.holidays', []);
        $weekendDate = Carbon::now()->next(Carbon::SATURDAY)->toDateString();

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Weekend',
            'slug' => 'cabang-weekend',
            'address' => 'Jl. Weekend',
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $weekendDate,
            ]))
            ->assertOk()
            ->assertJsonPath('is_closed', true);
    }

    public function test_store_rejects_holiday_booking_date(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.holidays', ['2026-12-25']);

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Libur',
            'slug' => 'cabang-libur',
            'address' => 'Jl. Libur',
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => '2026-12-25',
                'booking_time' => '13:00',
                'payment_type' => Booking::PAYMENT_TYPE_FULL,
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors('booking_date');
    }

    public function test_manager_can_add_manual_holiday_and_it_blocks_booking_date(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.holidays', []);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Manual Holiday',
            'slug' => 'cabang-manual-holiday',
            'address' => 'Jl. Manual Holiday',
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->post(route('admin.locations.holidays.store'), [
                'studio_location_id' => $location->id,
                'holiday_date' => '2026-08-17',
                'name' => 'Libur Kemerdekaan',
                'notes' => 'Studio tutup',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('studio_holidays', [
            'studio_location_id' => $location->id,
            'name' => 'Libur Kemerdekaan',
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => '2026-08-17',
            ]))
            ->assertOk()
            ->assertJsonPath('is_closed', true);
    }

    public function test_manual_holiday_only_blocks_the_selected_location(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.holidays', []);

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $blockedLocation = StudioLocation::create([
            'name' => 'Cabang Ditutup',
            'slug' => 'cabang-ditutup',
            'address' => 'Jl. Ditutup',
            'is_active' => true,
        ]);
        $openLocation = StudioLocation::create([
            'name' => 'Cabang Tetap Buka',
            'slug' => 'cabang-tetap-buka',
            'address' => 'Jl. Tetap Buka',
            'is_active' => true,
        ]);

        StudioHoliday::create([
            'studio_location_id' => $blockedLocation->id,
            'holiday_date' => '2026-08-17',
            'name' => 'Renovasi Cabang',
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $blockedLocation->id,
                'booking_date' => '2026-08-17',
            ]))
            ->assertOk()
            ->assertJsonPath('is_closed', true);

        $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $openLocation->id,
                'booking_date' => '2026-08-17',
            ]))
            ->assertOk()
            ->assertJsonPath('is_closed', false);
    }
}
