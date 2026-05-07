<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
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
        $bookingDate = Carbon::now()->addDays(2)->toDateString();

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $location = StudioLocation::create([
            'name' => 'Cabang Availability',
            'slug' => 'cabang-availability',
            'address' => 'Jl. Ketersediaan',
            'is_active' => true,
        ]);
        $room = StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio 1',
            'is_active' => true,
        ]);

        Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
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
        $this->assertNotContains('13:00', $times);
        $this->assertNotContains('15:00', $times);
        $this->assertContains('12:45', $times);
        $this->assertContains('15:15', $times);
    }

    public function test_availability_uses_active_room_capacity_inside_selected_location(): void
    {
        config()->set('studio.closed_weekdays', []);

        $bookingDate = Carbon::now()->addDays(3)->toDateString();
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $location = StudioLocation::create([
            'name' => 'Cabang Multi Room',
            'slug' => 'cabang-multi-room',
            'address' => 'Jl. Multi Room',
            'is_active' => true,
        ]);
        $roomA = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio A', 'is_active' => true]);
        $roomB = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio B', 'is_active' => true]);

        Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $roomA->id,
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

        $this->assertContains('14:00', collect($response->json('available_times'))->pluck('value')->all());

        Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $roomB->id,
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

        $this->assertNotContains('14:00', collect($response->json('available_times'))->pluck('value')->all());
    }

    public function test_extra_time_addon_extends_duration_for_availability(): void
    {
        config()->set('studio.closed_weekdays', []);
        config()->set('studio.slot_interval_minutes', 15);
        config()->set('studio.booking_buffer_minutes', 15);

        $bookingDate = Carbon::now()->addDays(4)->toDateString();
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create([
            'duration_minutes' => 30,
            'addons' => [
                ['label' => 'Tambah waktu', 'price' => 100000, 'is_active' => true],
            ],
        ]);
        $location = StudioLocation::create([
            'name' => 'Cabang Extra Time',
            'slug' => 'cabang-extra-time',
            'address' => 'Jl. Extra Time',
            'is_active' => true,
        ]);
        $room = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio A', 'is_active' => true]);

        Booking::factory()->create([
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'studio_room_id' => $room->id,
            'booking_date' => $bookingDate,
            'booking_time' => '13:45',
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $bookingDate,
            ]))
            ->assertOk();

        $this->assertContains('13:00', collect($response->json('available_times'))->pluck('value')->all());

        $addonKey = md5('Tambah waktu|100000');
        $response = $this->actingAs($client)
            ->getJson(route('bookings.availability', [
                'package_id' => $package->id,
                'studio_location_id' => $location->id,
                'booking_date' => $bookingDate,
                'selected_addons' => [$addonKey],
                'addon_quantities' => [$addonKey => 1],
            ]))
            ->assertOk()
            ->assertJsonPath('base_duration_minutes', 30)
            ->assertJsonPath('extra_duration_minutes', 10)
            ->assertJsonPath('duration_minutes', 40);

        $this->assertNotContains('13:00', collect($response->json('available_times'))->pluck('value')->all());
    }

    public function test_availability_endpoint_hides_today_slots_that_have_already_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 15:00:00'));

        try {
            config()->set('studio.closed_weekdays', []);
            config()->set('studio.open_time', '11:00');
            config()->set('studio.close_time', '22:00');
            config()->set('studio.slot_interval_minutes', 15);

            $client = User::factory()->create(['role' => Role::CLIENT]);
            $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
            $location = StudioLocation::create([
                'name' => 'Cabang Hari Ini',
                'slug' => 'cabang-hari-ini',
                'address' => 'Jl. Hari Ini',
                'is_active' => true,
            ]);
            StudioRoom::create([
                'studio_location_id' => $location->id,
                'name' => 'Studio 1',
                'is_active' => true,
            ]);

            $response = $this->actingAs($client)
                ->getJson(route('bookings.availability', [
                    'package_id' => $package->id,
                    'studio_location_id' => $location->id,
                    'booking_date' => Carbon::now()->toDateString(),
                ]))
                ->assertOk()
                ->assertJsonPath('is_today', true)
                ->assertJsonPath('current_time', '15:00');

            $times = collect($response->json('available_times'))->pluck('value')->all();

            $this->assertNotContains('11:00', $times);
            $this->assertNotContains('12:00', $times);
            $this->assertNotContains('13:00', $times);
            $this->assertNotContains('14:00', $times);
            $this->assertNotContains('15:00', $times);
            $this->assertContains('15:15', $times);
            $this->assertContains('16:00', $times);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_store_rejects_today_booking_time_that_has_already_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 15:00:00'));

        try {
            config()->set('studio.closed_weekdays', []);
            config()->set('studio.open_time', '11:00');
            config()->set('studio.close_time', '22:00');
            config()->set('studio.slot_interval_minutes', 15);

            $client = User::factory()->create(['role' => Role::CLIENT]);
            $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
            $location = StudioLocation::create([
                'name' => 'Cabang Tolak Jam Lewat',
                'slug' => 'cabang-tolak-jam-lewat',
                'address' => 'Jl. Tolak Jam Lewat',
                'is_active' => true,
            ]);
            StudioRoom::create([
                'studio_location_id' => $location->id,
                'name' => 'Studio 1',
                'is_active' => true,
            ]);

            $this->actingAs($client)
                ->from(route('bookings.create'))
                ->post(route('bookings.store'), [
                    'package_id' => $package->id,
                    'studio_location_id' => $location->id,
                    'booking_date' => Carbon::now()->toDateString(),
                    'booking_time' => '15:00',
                    'payment_type' => Booking::PAYMENT_TYPE_FULL,
                ])
                ->assertRedirect(route('bookings.create'))
                ->assertSessionHasErrors('booking_time');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_availability_endpoint_marks_weekend_as_closed(): void
    {
        config()->set('studio.closed_weekdays', [0, 6]);
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
}
