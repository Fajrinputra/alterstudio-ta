<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessRulesSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_deactivate_user_with_active_project(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $photographer = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
            'roles' => [Role::PHOTOGRAPHER->value],
            'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $category = ServiceCategory::create(['name' => 'Wedding']);
        $package = ServicePackage::factory()->create(['category_id' => $category->id]);
        $location = StudioLocation::create(['name' => 'Cabang A', 'slug' => 'cabang-a', 'is_active' => true]);

        $booking = Booking::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(1),
            'booking_time' => '13:00',
            'status' => Booking::STATUS_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'addon_total' => 0,
            'total_price' => 200000,
        ]);

        Project::create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($manager)
            ->post(route('admin.users.toggle', $photographer), ['is_active' => 0])
            ->assertRedirect();

        $this->assertTrue($photographer->fresh()->is_active);
    }

    public function test_user_cannot_delete_own_account_when_booking_or_project_is_active(): void
    {
        $client = User::factory()->create([
            'role' => Role::CLIENT,
            'password' => bcrypt('password'),
        ]);
        $category = ServiceCategory::create(['name' => 'Graduation']);
        $package = ServicePackage::factory()->create(['category_id' => $category->id]);
        $location = StudioLocation::create(['name' => 'Cabang B', 'slug' => 'cabang-b', 'is_active' => true]);

        $booking = Booking::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(2),
            'booking_time' => '15:00',
            'status' => Booking::STATUS_PAID,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'addon_total' => 0,
            'total_price' => 300000,
        ]);

        Project::create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_EDITING,
        ]);

        $this->actingAs($client)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', ['id' => $client->id]);
    }

    public function test_expired_waiting_payment_booking_is_cancelled_automatically(): void
    {
        Carbon::setTestNow(now());

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $category = ServiceCategory::create(['name' => 'Family']);
        $package = ServicePackage::factory()->create(['category_id' => $category->id]);
        $location = StudioLocation::create(['name' => 'Cabang C', 'slug' => 'cabang-c', 'is_active' => true]);

        $booking = Booking::query()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDays(3),
            'booking_time' => '12:00',
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'payment_type' => Booking::PAYMENT_TYPE_FULL,
            'addon_total' => 0,
            'total_price' => 400000,
        ]);

        $booking->forceFill([
            'created_at' => now()->subMinutes(31),
            'updated_at' => now()->subMinutes(31),
        ])->saveQuietly();

        Payment::create([
            'booking_id' => $booking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 400000,
            'status' => Payment::STATUS_PENDING,
            'order_id' => 'ORDER-TEST-1',
            'snap_token' => 'SNAP-TEST',
        ]);

        $this->artisan('bookings:cancel-expired')
            ->assertExitCode(0);

        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $booking->payments()->first()->status);

        Carbon::setTestNow();
    }
}
