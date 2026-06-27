<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UseCaseScenarioA12A21Test extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_visible_only_to_client_admin_manager_and_hides_inactive_packages(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Family']);
        $active = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Paket Aktif',
            'is_active' => true,
        ]);
        $inactive = ServicePackage::factory()->create([
            'category_id' => $category->id,
            'name' => 'Paket Nonaktif',
            'is_active' => false,
        ]);

        foreach ([Role::CLIENT, Role::ADMIN, Role::MANAGER] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('catalog.public'))
                ->assertOk()
                ->assertSee('Family')
                ->assertSee('Paket Aktif')
                ->assertDontSee('Paket Nonaktif');
        }

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $this->actingAs($client)
            ->get(route('catalog.package.show', $active))
            ->assertOk();
        $this->actingAs($client)
            ->get(route('catalog.package.show', $inactive))
            ->assertNotFound();

        foreach ([Role::OWNER, Role::PHOTOGRAPHER, Role::EDITOR] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('catalog.public'))
                ->assertForbidden();
        }
    }

    public function test_admin_and_manager_can_create_catalog_with_initial_package(): void
    {
        foreach ([Role::ADMIN, Role::MANAGER] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $suffix = strtolower($role->value);

            $this->actingAs($user)
                ->post(route('admin.catalog.store'), [
                    'name' => 'Kategori '.$suffix,
                    'description' => 'Deskripsi kategori',
                    'packages' => [[
                        'name' => 'Paket '.$suffix,
                        'price' => 250000,
                    ]],
                ])
                ->assertRedirect(route('admin.catalog'));

            $this->assertDatabaseHas('service_categories', ['name' => 'Kategori '.$suffix]);
            $this->assertDatabaseHas('service_packages', ['name' => 'Paket '.$suffix]);
        }

        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->actingAs($admin)
            ->post(route('admin.catalog.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_category_update_and_delete_follow_validation_and_package_guard(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $category = ServiceCategory::factory()->create(['name' => 'Kategori Awal']);

        $this->actingAs($manager)
            ->putJson('/admin/categories/'.$category->id, ['name' => ''])
            ->assertUnprocessable();

        $this->actingAs($manager)
            ->putJson('/admin/categories/'.$category->id, [
                'name' => 'Kategori Baru',
                'description' => 'Diperbarui',
            ])
            ->assertOk();

        $package = ServicePackage::factory()->create(['category_id' => $category->id]);

        $this->actingAs($manager)
            ->deleteJson('/admin/categories/'.$category->id)
            ->assertUnprocessable();
        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);

        $package->forceDelete();

        $this->actingAs($manager)
            ->deleteJson('/admin/categories/'.$category->id)
            ->assertOk();
        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }

    public function test_package_create_update_and_file_validation_work_for_manager(): void
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $category = ServiceCategory::factory()->create();

        $this->actingAs($manager)
            ->from(route('admin.catalog.packages', $category))
            ->post(route('admin.packages.store'), [
                'category_id' => $category->id,
                'name' => 'Paket Baru',
                'price' => 300000,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.catalog.packages', $category));

        $package = ServicePackage::where('name', 'Paket Baru')->firstOrFail();

        $this->actingAs($manager)
            ->from(route('admin.packages.edit', $package))
            ->put(route('admin.packages.update', $package), [
                'category_id' => $category->id,
                'name' => 'Paket Baru',
                'price' => 300000,
                'overview_image' => UploadedFile::fake()->create('invalid.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('overview_image');

        $this->actingAs($manager)
            ->from(route('admin.catalog.packages', $category))
            ->put(route('admin.packages.update', $package), [
                'category_id' => $category->id,
                'name' => 'Paket Diperbarui',
                'price' => 350000,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.catalog.packages', $category));

        $this->assertDatabaseHas('service_packages', [
            'id' => $package->id,
            'name' => 'Paket Diperbarui',
        ]);
    }

    public function test_package_in_active_work_is_deactivated_but_completed_package_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $activePackage = ServicePackage::factory()->create(['is_active' => true]);
        $activeBooking = Booking::factory()->create([
            'package_id' => $activePackage->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::create(['booking_id' => $activeBooking->id, 'status' => Project::STATUS_DRAFT]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.packages.destroy', $activePackage))
            ->assertUnprocessable();
        $this->assertFalse($activePackage->fresh()->is_active);

        $completedPackage = ServicePackage::factory()->create(['is_active' => true]);
        $completedBooking = Booking::factory()->create([
            'package_id' => $completedPackage->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::create(['booking_id' => $completedBooking->id, 'status' => Project::STATUS_FINAL]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.packages.destroy', $completedPackage))
            ->assertOk();
        $this->assertSoftDeleted('service_packages', ['id' => $completedPackage->id]);
    }

    public function test_admin_and_manager_can_manage_booking_status(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => Role::CLIENT]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);

        $this->actingAs($manager)
            ->get('/admin/bookings')
            ->assertOk()
            ->assertSee('Konfirmasi Pemesanan')
            ->assertSee('Tolak Pemesanan');
        $this->actingAs($manager)
            ->post(route('admin.bookings.status', $booking), [
                'status' => Booking::STATUS_WAITING_PAYMENT,
            ])
            ->assertRedirect();

        $this->assertNotNull($booking->fresh()->confirmed_at);
        Notification::assertSentTo($client, BookingConfirmedNotification::class);

        $adminBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.status', $adminBooking), [
                'status' => Booking::STATUS_WAITING_PAYMENT,
            ])
            ->assertRedirect();

        $this->assertNotNull($adminBooking->fresh()->confirmed_at);

        $rejected = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
        ]);

        $this->actingAs($manager)
            ->post(route('admin.bookings.status', $rejected), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect();
        $this->assertSame(Booking::STATUS_CANCELLED, $rejected->fresh()->status);

        $confirmedToCancel = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('admin.bookings.status', $confirmedToCancel), [
                'status' => Booking::STATUS_CANCELLED,
            ])
            ->assertRedirect();
        $this->assertSame(Booking::STATUS_CANCELLED, $confirmedToCancel->fresh()->status);

        $dpPaid = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_DP_PAID,
            'confirmed_at' => now(),
            'payment_type' => Booking::PAYMENT_TYPE_DP,
            'total_price' => 1000000,
        ]);
        \App\Models\Payment::create([
            'booking_id' => $dpPaid->id,
            'type' => \App\Models\Payment::TYPE_DP,
            'amount' => 100000,
            'status' => \App\Models\Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('admin.bookings.status', $dpPaid), [
                'status' => Booking::STATUS_PAID,
            ])
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_PAID, $dpPaid->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $dpPaid->id,
            'type' => \App\Models\Payment::TYPE_FULL,
            'amount' => 900000,
            'status' => \App\Models\Payment::STATUS_PAID,
            'reference' => 'manual_onsite_settlement',
        ]);

        $this->actingAs($manager)
            ->post(route('admin.bookings.status', $rejected), [
                'status' => Booking::STATUS_PAID,
            ])
            ->assertSessionHas('error');
        $this->assertSame(Booking::STATUS_CANCELLED, $rejected->fresh()->status);
    }
}
