<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_active_catalog_locations_and_hero_slides(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Family']);
        $activePackage = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Big Family',
            'is_active' => true,
        ]);
        $inactivePackage = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hidden Package',
            'is_active' => false,
        ]);
        $location = StudioLocation::create([
            'name' => 'Cabang Publik',
            'slug' => 'cabang-publik',
            'address' => 'Jl. Publik',
            'is_active' => true,
        ]);
        $slide = LandingHeroSlide::create([
            'title' => 'Alter Studio',
            'image_path' => 'landing/hero/a.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('categories')
            ->assertViewHas('locations')
            ->assertViewHas('heroSlides');

        $categories = $response->viewData('categories');
        $this->assertTrue($categories->first()->packages->contains('id', $activePackage->id));
        $this->assertFalse($categories->first()->packages->contains('id', $inactivePackage->id));
        $this->assertTrue($response->viewData('locations')->contains('id', $location->id));
        $this->assertTrue($response->viewData('heroSlides')->contains('id', $slide->id));
    }

    public function test_public_location_detail_loads_rooms_and_photos(): void
    {
        $location = StudioLocation::create([
            'name' => 'Cabang Detail',
            'slug' => 'cabang-detail',
            'address' => 'Jl. Detail',
            'photo_gallery' => ['locations/1/front.jpg'],
            'is_active' => true,
        ]);
        StudioRoom::create([
            'studio_location_id' => $location->id,
            'name' => 'Studio A',
            'is_active' => true,
        ]);

        $response = $this->get(route('locations.public.show', $location));

        $response->assertOk()
            ->assertViewHas('location')
            ->assertViewHas('photos', ['locations/1/front.jpg'])
            ->assertSee('Cabang Detail', false)
            ->assertSee('Studio A', false);
    }

    public function test_dashboard_data_changes_by_role(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        User::factory()->create(['role' => Role::PHOTOGRAPHER, 'is_active' => true]);
        User::factory()->create(['role' => Role::EDITOR, 'is_active' => true]);

        $managerResponse = $this->actingAs($manager)->get(route('dashboard'));
        $managerResponse->assertOk()->assertViewHas('role', Role::MANAGER);
        $this->assertArrayHasKey('roleCounts', $managerResponse->viewData('data'));

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Dashboard',
            'slug' => 'cabang-dashboard',
            'address' => 'Jl. Dashboard',
            'is_active' => true,
        ]);
        Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        $clientResponse = $this->actingAs($client)->get(route('dashboard'));
        $clientResponse->assertOk()->assertViewHas('role', Role::CLIENT);
        $this->assertSame(1, $clientResponse->viewData('data')['metrics']['bookings']);
        $this->assertSame(1, $clientResponse->viewData('data')['metrics']['waiting_payment']);

        $owner = User::factory()->create(['role' => Role::OWNER]);
        $paidBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
            'total_price' => 750000,
        ]);
        Payment::create([
            'booking_id' => $paidBooking->id,
            'type' => Payment::TYPE_FULL,
            'amount' => 750000,
            'status' => Payment::STATUS_PAID,
            'order_id' => 'DASHBOARD-OWNER-1',
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $ownerResponse = $this->actingAs($owner)->get(route('dashboard'));
        $ownerResponse->assertOk()
            ->assertViewHas('role', Role::OWNER)
            ->assertSee('Pendapatan Diterima', false)
            ->assertSee('Rp 750.000', false);
        $this->assertSame(750000.0, $ownerResponse->viewData('data')['metrics']['revenue_received']);
    }

    public function test_dual_crew_dashboard_contains_photographer_and_editor_sections(): void
    {
        $crew = User::factory()->create([
            'role' => Role::PHOTOGRAPHER,
            'roles' => [Role::EDITOR->value],
        ]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Dual Crew',
            'slug' => 'cabang-dual-crew',
            'address' => 'Jl. Dual Crew',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::factory()->create([
            'booking_id' => $booking->id,
            'photographer_id' => $crew->id,
            'editor_id' => $crew->id,
            'status' => Project::STATUS_EDITING,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'edit_requested_at' => now(),
        ]);

        $response = $this->actingAs($crew)->get(route('dashboard'));

        $response->assertOk();
        $this->assertTrue($response->viewData('hasBothCrewRoles'));
        $this->assertArrayHasKey('upcoming', $response->viewData('data'));
        $this->assertArrayHasKey('queue', $response->viewData('data'));
    }
}
